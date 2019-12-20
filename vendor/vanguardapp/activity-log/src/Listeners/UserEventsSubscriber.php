<?php

namespace Vanguard\UserActivity\Listeners;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Lab404\Impersonate\Events\LeaveImpersonation;
use Lab404\Impersonate\Events\TakeImpersonation;
use Vanguard\Events\Settings\Updated as SettingsUpdated;
use Vanguard\Events\User\Banned;
use Vanguard\Events\User\ChangedAvatar;
use Vanguard\Events\User\Created;
use Vanguard\Events\User\Deleted;
use Vanguard\Events\User\LoggedIn;
use Vanguard\Events\User\LoggedOut;
use Vanguard\Events\User\RequestedPasswordResetEmail;
use Vanguard\Events\User\TwoFactorDisabled;
use Vanguard\Events\User\TwoFactorDisabledByAdmin;
use Vanguard\Events\User\TwoFactorEnabled;
use Vanguard\Events\User\TwoFactorEnabledByAdmin;
use Vanguard\Events\User\UpdatedByAdmin;
use Vanguard\Events\User\UpdatedProfileDetails;
use Vanguard\UserActivity\Logger;

class UserEventsSubscriber
{
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function onLogin(LoggedIn $event)
    {
        $this->logger->log(trans('user-activity::log.logged_in'));
    }

    public function onLogout(LoggedOut $event)
    {
        $this->logger->log(trans('user-activity::log.logged_out'));
    }

    public function onRegister(Registered $event)
    {
        $this->logger->setUser($event->user);
        $this->logger->log(trans('user-activity::log.created_account'));
    }

    public function onAvatarChange(ChangedAvatar $event)
    {
        $this->logger->log(trans('user-activity::log.updated_avatar'));
    }

    public function onProfileDetailsUpdate(UpdatedProfileDetails $event)
    {
        $this->logger->log(trans('user-activity::log.updated_profile'));
    }

    public function onDelete(Deleted $event)
    {
        $message = trans(
            'user-activity::log.deleted_user',
            ['name' => $event->getDeletedUser()->present()->nameOrEmail]
        );

        $this->logger->log($message);
    }

    public function onBan(Banned $event)
    {
        $message = trans(
            'user-activity::log.banned_user',
            ['name' => $event->getBannedUser()->present()->nameOrEmail]
        );

        $this->logger->log($message);
    }

    public function onUpdateByAdmin(UpdatedByAdmin $event)
    {
        $message = trans(
            'user-activity::log.updated_profile_details_for',
            ['name' => $event->getUpdatedUser()->present()->nameOrEmail]
        );

        $this->logger->log($message);
    }

    public function onCreate(Created $event)
    {
        $message = trans(
            'user-activity::log.created_account_for',
            ['name' => $event->getCreatedUser()->present()->nameOrEmail]
        );

        $this->logger->log($message);
    }

    public function onSettingsUpdate(SettingsUpdated $event)
    {
        $this->logger->log(trans('user-activity::log.updated_settings'));
    }

    public function onTwoFactorEnable(TwoFactorEnabled $event)
    {
        $this->logger->log(trans('user-activity::log.enabled_2fa'));
    }

    public function onTwoFactorDisable(TwoFactorDisabled $event)
    {
        $this->logger->log(trans('user-activity::log.disabled_2fa'));
    }

    public function onTwoFactorEnableByAdmin(TwoFactorEnabledByAdmin $event)
    {
        $message = trans(
            'user-activity::log.enabled_2fa_for',
            ['name' => $event->getUser()->present()->nameOrEmail]
        );

        $this->logger->log($message);
    }

    public function onTwoFactorDisableByAdmin(TwoFactorDisabledByAdmin $event)
    {
        $message = trans(
            'user-activity::log.disabled_2fa_for',
            ['name' => $event->getUser()->present()->nameOrEmail]
        );

        $this->logger->log($message);
    }

    public function onPasswordResetEmailRequest(RequestedPasswordResetEmail $event)
    {
        $this->logger->setUser($event->getUser());
        $this->logger->log(trans('user-activity::log.requested_password_reset'));
    }

    public function onPasswordReset(PasswordReset $event)
    {
        $this->logger->setUser($event->user);
        $this->logger->log(trans('user-activity::log.reseted_password'));
    }

    public function onStartImpersonating(TakeImpersonation $event)
    {
        $this->logger->setUser($event->impersonator);

        $message = trans('user-activity::log.started_impersonating', [
            'id' => $event->impersonated->id,
            'name' => $event->impersonated->present()->name
        ]);

        $this->logger->log($message);
    }

    public function onStopImpersonating(LeaveImpersonation $event)
    {
        $this->logger->setUser($event->impersonator);

        $message = trans('user-activity::log.stopped_impersonating', [
            'id' => $event->impersonated->id,
            'name' => $event->impersonated->present()->name
        ]);

        $this->logger->log($message);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $class = self::class;

        $events->listen(LoggedIn::class, "{$class}@onLogin");
        $events->listen(LoggedOut::class, "{$class}@onLogout");
        $events->listen(Registered::class, "{$class}@onRegister");
        $events->listen(Created::class, "{$class}@onCreate");
        $events->listen(ChangedAvatar::class, "{$class}@onAvatarChange");
        $events->listen(UpdatedProfileDetails::class, "{$class}@onProfileDetailsUpdate");
        $events->listen(UpdatedByAdmin::class, "{$class}@onUpdateByAdmin");
        $events->listen(Deleted::class, "{$class}@onDelete");
        $events->listen(Banned::class, "{$class}@onBan");
        $events->listen(SettingsUpdated::class, "{$class}@onSettingsUpdate");
        $events->listen(TwoFactorEnabled::class, "{$class}@onTwoFactorEnable");
        $events->listen(TwoFactorDisabled::class, "{$class}@onTwoFactorDisable");
        $events->listen(TwoFactorEnabledByAdmin::class, "{$class}@onTwoFactorEnableByAdmin");
        $events->listen(TwoFactorDisabledByAdmin::class, "{$class}@onTwoFactorDisableByAdmin");
        $events->listen(RequestedPasswordResetEmail::class, "{$class}@onPasswordResetEmailRequest");
        $events->listen(PasswordReset::class, "{$class}@onPasswordReset");
        $events->listen(TakeImpersonation::class, "{$class}@onStartImpersonating");
        $events->listen(LeaveImpersonation::class, "{$class}@onStopImpersonating");
    }
}
