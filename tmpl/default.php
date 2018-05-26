<?php

defined('_JEXEC') or die;
use Joomla\Registry\Registry;

$customParams  = $this->params;
$general       = JPluginHelper::getPlugin('jagdpr', 'general');
$generalParams = new Registry($general->params);
$lang          = JFactory::getLanguage();
$currentLang   = $lang->getTag();
$request_send  = $this->getRequestPending();
$user          = KunenaUserHelper::getMyself();
$rankImage     = $user->getRank(0, 'image');
$rankTitle     = $user->getRank(0, 'title');
$version = KunenaForum::version();
$socials       = Joomla\Utilities\ArrayHelper::toObject($this->socialButtons());
$posts         = KunenaUserHelper::get($user->userid)->posts;
$doc           = JFactory::getDocument();
$title         = $customParams->get('title', array());
$info          = $customParams->get('header', array());
KunenaFactory::loadLanguage('com_kunena.templates', 'site');

if (!$user->exists())
{
	return;
}

if (!$customParams->get('status', 1))
{
	return '';
}

?>

<fieldset>
	<div class="panel panel-kunena has-avatar">
		<!-- Panel header -->
		<?php if (!empty($title->$currentLang)): ?>
			<div class="panel-head">
				<h2><?php echo $title->$currentLang; ?></h2>
			</div>
		<?php endif; ?>
		<!-- End: Panel header -->
		<?php if (!empty($info->$currentLang)): ?>
			<div class="alert alert-success">
				<div class="header-text">
					<?php echo $info->$currentLang; ?>
				</div>
			</div>
		<?php endif; ?>
		<!-- Panel body -->
		<div class="panel-body">
			<div class="user-quick-info">
				<?php if ($user->exists()) : ?>
					<span class="user-avatar">
						<?php echo $user->getAvatarImage(KunenaFactory::getTemplate()->params->get('avatarType'), 'posts'); ?>
					</span>
				<?php endif; ?>
				<strong class="user-username"><?php echo $user->username; ?></strong>
			</div>

			<div class="user-info-wrap">
				<div class="col-left">
					<div class="form-group">
						<div class="control-label">
							<label class="hasPopover" title="<?php echo $user->name; ?>">
								<?php echo JText::_('COM_KUNENA_REALNAME'); ?>
							</label>
						</div>
						<div class="controls">
							<?php echo $user->name; ?>
						</div>
					</div>

					<div class="form-group">
						<div class="control-label">
							<label class="hasPopover" title="">
								<?php echo JText::_('COM_KUNENA_MYPROFILE_THANKYOU_RECEIVED'); ?>
							</label>
						</div>
						<div class="controls">
							<?php echo $user->thankyou; ?>
						</div>
					</div>

					<div class="form-group">
						<div class="control-label">
							<label class="hasPopover" title="">
								<?php echo JText::_('COM_KUNENA_MYPROFILE_REGISTERDATE'); ?>
							</label>
						</div>
						<div class="controls">
							<?php echo $user->getRegisterDate(); ?>
						</div>
					</div>

					<div class="form-group">
						<div class="control-label">
							<label class="hasPopover" title="">
								<?php echo JText::_('COM_KUNENA_MYPROFILE_RANK'); ?>
							</label>
						</div>
						<div class="controls">
							<span>
								<?php echo $rankTitle; ?><br/>
								<?php echo $rankImage; ?>
							</span>
						</div>
					</div>

					<div class="form-group">
						<div class="control-label">
							<label class="hasPopover" title="">
								<?php echo JText::_('COM_KUNENA_MYPROFILE_POSTS'); ?>
							</label>
						</div>
						<div class="controls">
							<?php echo $posts; ?>
							<a href="<?php echo JRoute::_('index.php?option=com_kunena&view=topics&layout=user&mode=posts&userid=' . $user->userid); ?>">
								(<?php echo JText::_('COM_JAGDPR_KUNENA_ALL_POSTS'); ?>)
							</a>
						</div>
					</div>
				</div>

				<div class="col-right">
					<div class="form-group">
						<div class="control-label">
							<label class="hasPopover" title="">
								<?php echo JText::_('COM_KUNENA_MYPROFILE_LASTVISITDATE'); ?>
							</label>
						</div>
						<div class="controls">
							<?php echo $user->getLastVisitDate(); ?>
						</div>
					</div>

					<div class="form-group">
						<div class="control-label">
							<label class="hasPopover" title="">
								<?php echo JText::_('COM_KUNENA_USRL_KARMA'); ?>
							</label>
						</div>
						<div class="controls">
							<?php echo $user->getKarma(); ?>
						</div>
					</div>

					<div class="form-group">
						<div class="control-label">
							<label class="hasPopover" title="">
								<?php echo JText::_('COM_KUNENA_MYPROFILE_WEBSITE_NAME'); ?>
							</label>
						</div>
						<div class="controls">
							<?php echo $user->getWebsiteLink(); ?>
						</div>
					</div>

					<div class="form-group">
						<div class="control-label">
							<label class="hasPopover" title="">
								<?php echo JText::_('COM_JAGDPR_KUNENA_SOCIALS'); ?>
							</label>
						</div>
						<div class="controls">
							<ul class="social-list">
								<?php foreach ($socials as $key => $social): ?>
									<?php if (!empty($user->$key)) : ?>
										<li class="hasPopover" title="<?php echo $user->$key; ?>">
											<span 
												class="kicon-profile kicon-profile-<?php echo $key; ?> <?php echo KunenaTemplate::getInstance()->tooltips() ?>" 
												title="<?php echo $user->$key ?>"></span>
										</li>
									<?php endif; ?>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="panel-footer">
			<div class="form-group actions-wrap">
				<div class="controls">
					<a class="btn btn-primary"
					   href="<?php echo KunenaProfile::getInstance()->getEditProfileURL($user->userid); ?>">
						<i class="fa fa-edit"></i><?php echo JText::_('JA_GDPR_EDIT_PROFILE'); ?>
					</a>

					<?php if ( $customParams->get('show_del_req_button')): ?>
						<?php if ( $generalParams->get('delete_all') == 1 ): ?>
						<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#kunenaModal">
							<i class="fa fa-remove"></i><?php echo JText::_('JA_GDPR_PERMANENT_DELETE'); ?>
						</button>
						<?php elseif ( $generalParams->get('delete_all') == 2 ): ?>
							<button 
								type="button" 
								class="<?php echo $request_send ? 'disabled ': ''; ?> btn btn-warning action-redirect"
								<?php echo $request_send ? '': 'data-toggle="modal" data-target="#kunenaRequestModal"'; ?>>
							  <i class="fa fa-remove"></i> <?php echo JText::_('JA_GDPR_REQUEST_DELETE_ALL_ACCOUNT'); ?>
							</button>
						<?php endif; ?>
					<?php endif; ?>
					
				</div>
			</div>
		</div>
	</div>
</fieldset>

<div id="kunenaModal" class="modal fade">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title"><?php echo JText::_('JA_GDPR_DELETE_ACCOUNTS'); ?></h4>
			</div>
			<div class="modal-body">
				<?php if (!empty($generalParams->get('notify')->$currentLang)): ?>
					<div class="notify-text">
						<?php echo $generalParams->get('notify')->$currentLang; ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default"
				        data-dismiss="modal"><?php echo JText::_('JCLOSE'); ?>
				</button>
				<form style="display:inline;" action="<?php echo JRoute::_('index.php?option=com_jagdpr', false); ?>"
				      method="post">
					<button type="submit" class="btn btn-primary">
						<i class="fa fa-remove"></i> <?php echo JText::_('JDELETE'); ?>
					</button>
					<input type="hidden" name="task" value="collection.jacustom"/>
					<input type="hidden" name="action" value="deleteuser"/>
					<input type="hidden" name="plugin" value="kunena"/>
					<input type="hidden" name="step" value="confirm" />
					<?php echo JHtml::_('form.token'); ?>
				</form>
			</div>
		</div>
	</div>
</div>

<div id="kunenaRequestModal" class="modal fade">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title"><?php echo JText::_('JA_GDPR_REQUEST_DELETE_ALL_ACCOUNT'); ?></h4>
			</div>
			<div class="modal-body">
				<?php if (!empty($generalParams->get('notify')->$currentLang)): ?>
					<div class="notify-text">
						<?php echo $generalParams->get('notify')->$currentLang; ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default"
				        data-dismiss="modal"><?php echo JText::_('JCLOSE'); ?>
				</button>
				<form style="display:inline;" action="<?php echo JRoute::_('index.php?option=com_jagdpr', false); ?>"
				      method="post">
					<button type="submit" class="btn btn-primary">
						<i class="fa fa-remove"></i> <?php echo JText::_('JA_GDPR_REQUEST_DELETE_ALL_ACCOUNT'); ?>
					</button>
					<input type="hidden" name="task" value="collection.jacustom"/>
					<input type="hidden" name="action" value="requestdelete"/>
					<input type="hidden" name="plugin" value="kunena"/>
					<input type="hidden" name="step" value="confirm" />
					<?php echo JHtml::_('form.token'); ?>
				</form>
			</div>
		</div>
	</div>
</div>

<style>
	
.kicon-profile {
  width: 32px;
  height: 32px;
  display: inline-block;
  vertical-align: text-top;
  margin-right: 3px;
  background: url(/components/com_kunena/template/crypsis/assets/iconsets/profile/default/default.png) no-repeat;
}
.inline .kicon-profile {
  width: 32px;
  height: 32px;
}
.img-circle {
  margin-top: 0.5em;
  position: relative;
  -webkit-border-radius: 50%;
  -moz-border-radius: 50%;
  border-radius: 50%;
  -webkit-box-shadow: 0 0 0 3px #fff, 0 0 0 4px #ccc, 0 2px 5px 4px rgba(0,0,0,0.1);
  -moz-box-shadow: 0 0 0 3px #fff, 0 0 0 4px #ccc, 0 2px 5px 4px rgba(0,0,0,0.1);
  box-shadow: 0 0 0 3px #fff, 0 0 0 4px #ccc, 0 2px 5px 4px rgba(0,0,0,0.1);
}
.kicon-profile-myspace {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/myspace.png') 50% 50%;
}
.kicon-profile-digg {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/digg.png') 50% 50%;
}
.kicon-profile-birthdate {
  background-position: 0 -42px;
}
.kicon-profile-plus {
  background-position: 0 -312px;
}
.kicon-profile-minus {
  background-position: 0 -294px;
}
.kicon-profile-twitter {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/twitter.png') 50% 50%;
}
.kicon-profile-pm {
  background-position: 0 -437px;
}
.kicon-profile-microsoft {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/microsoft.png') 50% 50%;
}
.kicon-profile-friendfeed {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/friendfeed.png') 50% 50%;
}
.kicon-profile-website {
  background-position: 0 -521px;
}
.kicon-profile-remind {
  background-position: 0 -458px;
}
.kicon-profile-google {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/google.png') 50% 50%;
}
.kicon-profile-gender-unknown {
  background-position: 0 -231px;
}
.kicon-profile-facebook {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/facebook.png') 50% 50%;
}
.kicon-profile-email {
  background-position: 0 -126px;
}
.kicon-profile-delicious {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/delicious.png') 50% 50%;
}
.kicon-profile-icq {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/icq.png') 50% 50%;
}
.kicon-profile-flickr {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/flickr.png') 50% 50%;
}
.kicon-profile-skype {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/skype.png') 50% 50%;
}
.kicon-profile-location {
  background-position: 0 -353px;
}
.kicon-profile-gender-male {
  background-position: 0 -374px;
}
.kicon-profile-linkedin {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/linkedin.png') 50% 50%;
}
.kicon-profile-yim {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/yim.png') 50% 50%;
}
#kunena.layout .kicon-profile-gender-female {
  background-position: 0 -168px;
}
.kicon-profile-blogspot {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/blogger.png') 50% 50%;
}
.kicon-profile-apple {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/apple.png') 50% 50%;
}
.kicon-profile-qq {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/qq.png') 50% 50%;
}
.kicon-profile-qzone {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/qzone.png') 50% 50%;
}
.kicon-profile-instagram {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/instagram.png') 50% 50%;
}
.kicon-profile-weibo {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/weibo.png') 50% 50%;
}
.kicon-profile-wechat {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/wechat.png') 50% 50%;
}
.kicon-profile-vk {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/vk.png') 50% 50%;
}
.kicon-profile-telegram {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/telegram.png') 50% 50%;
}
.kicon-profile-bebo {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/bebo.png') 50% 50%;
}
.kicon-profile-whatsapp {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/whatsapp.png') 50% 50%;
}
.kicon-profile-youtube {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/youtube.png') 50% 50%;
}
.kicon-profile-ok {
  background: url('<?php echo JUri::root() ?>components/com_kunena/template/crypsis/assets/images/social/ok.png') 50% 50%;
}
span.kicon-profile-website,
span.kicon-profile-pm {
  vertical-align: top;
  margin-top: 3px;
  margin-bottom: 3px;
}
span.kicon-profile-pm {
  margin-right: 0;
}
</style>