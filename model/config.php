<?php

namespace Identity\Model;

if(!defined("IdentityFramework")){ die("Access Denied!"); }

class Config
{
	/** pk, type: int, length: 11, default: 1, not null */
    public $Id;
    
    /** type: int, not null, default: 6 */
    public $PasswordMinimumLength = 6;

    /** type: int, not null, default: 4 */
    public $UsernameMinimumLength = 4;

    /** type: int, not null, default: 32 */
    public $SaltLength = 32;

    /** type: int, not null, default: 3 */
    public $LoginRememberDays = 3;

    /** type: int, not null, default: 3 */
    public $LoginMaxAttempts = 3;

    /** type: varchar, null, length: 100 */
    public $DateTimeFormat = "d.m.Y H:i:s";

    /** type: varchar, null, length: 100 */
    public $DateTimeFormatWithoutHours = "d.m.Y";

    /** type: varchar, null, length: 100 */
    public $DateTimeFormatWithoutSeconds = "d.m.Y H:i";

    /** type: bit, length: 1, default: 1, not null */
    public $UserCanRegister = true;

    /** type: bit, length: 1, default: 1, not null */
    public $UserCanLogin = true;

    /** type: bit, length: 1, default: 0, not null */
    public $UserRegistrationHasToActivate = false;

    /** type: varchar, length: 250, null */
    public $EmailFrom = null;

    /** type: varchar, length: 250, null */
    public $EmailFromName = null;

    /** type: varchar, length: 250, null */
    public $EmailReplayTo = null;

    /** type: bit, length: 1, default: 0, not null */
    public $MailSendUserRegistration = false;

    /** type: varchar, length: 500, null  */
    public $MailSendUserRegistrationSubject = null;

    /** type: text, null  */
    public $MailSendUserRegistrationContent = null;

    /** type: bit, length: 1, default: 0, not null */
    public $MailSendUserRegistrationIsHTML = false;

    /** type: varchar, length: 500, null  */
    public $MailSendUserActivationSubject = null;

    /** type: text, null  */
    public $MailSendUserActivationContent = null;

    /** type: bit, length: 1, default: 0, not null */
    public $MailSendUserActivationIsHTML = false;

    /** type: varchar, length: 500, null  */
    public $MailSendPasswordForgotSubject = null;

    /** type: text, null  */
    public $MailSendPasswordForgotContent = null;

    /** type: bit, length: 1, default: 0, not null */
    public $MailSendPasswordForgotIsHTML = false;
    
    /** type: varchar, length: 500, null  */
    public $MailSendPasswordResetSubject = null;

    /** type: text, null  */
    public $MailSendPasswordResetContent = null;

    /** type: bit, length: 1, default: 0, not null */
    public $MailSendPasswordResetIsHTML = false;

    /** type: varchar, length: 255, null */
    public $JoomlaPath = null;

    /** type: varchar, length: 255, null */
    public $WordPressPath;
}

?>