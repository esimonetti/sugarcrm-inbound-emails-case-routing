<?php
$manifest = array (
  'acceptable_sugar_versions' => array ('7.2.*', '6.5.*'),
  'acceptable_sugar_flavors' => 
  array (
    'PRO',
    'ENT',
    'ULT',
  ),
  'author' => 'Enrico Simonetti',
  'description' => 'This package allows code customisations of the Case routing rules for the Inbound Email to Case Sugar functionality.

The routing rules need to be coded on the installed logic hook sectioni and will apply only to Cases coming from Emails.


Enrico Simonetti
http://enricosimonetti.com
@simonettienrico',
  'is_uninstallable' => true,
  'name' => 'InboundEmailCustomCaseRouting',
  'published_date' => '2014-09-13 10:30',
  'type' => 'module',
  'version' => 1.0,
);


$installdefs = array(
  'id' => 'InboundEmailCustomCaseRouting',
  'language' => array(
    array(
      'from' => '<basepath>/include/language/en_us.InboundEmailCustomCaseRouting.php',
      'to_module' => 'application',
      'language' => 'en_us',
    ),
  ),
  'copy' => array(
    array(
      'from' => '<basepath>/custom/modules/Emails/inboundEmailsLogic.php',
      'to' => 'custom/modules/Emails/inboundEmailsLogic.php',
    ),
  ),
  'logic_hooks' => array(
    array(
      'module' => 'Emails',
      'hook' => 'before_save',
      'order' => 2,
      'description' => 'Reopen Case related to this new inbound email',
      'file' => 'custom/modules/Emails/inboundEmailsLogic.php',
      'class' => 'inboundEmailsLogic',
      'function' => 'reopenCase',
    ),
    array(
      'module' => 'Emails',
      'hook' => 'before_save',
      'order' => 3,
      'description' => 'Custom routing of a new Case from an Email',
      'file' => 'custom/modules/Emails/inboundEmailsLogic.php',
      'class' => 'inboundEmailsLogic',
      'function' => 'customCaseAssignment',
    ),
  ),
);
