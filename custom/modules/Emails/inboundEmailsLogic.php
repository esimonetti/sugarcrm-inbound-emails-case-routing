<?php if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*
	Enrico Simonetti

	http://enricosimonetti.com
	@simonettienrico

	13/09/2014

	Tested on SugarCRM 6.5.17 and SugarCRM 7.2.2.0
	without other workflows on Emails/Cases

	Before use:
	1) Create a new empty team in Sugar
	2) Create an Inbound email to Case mailbox and assign all cases to the empty team of step 1
	3) Create a new case status if required to know when there is a new email for a Case
	4) Set correctly variables on the "Configuration variables" section of the code
	5) Customise your own case and email assignment logic on the "CUSTOM ASSIGNMENT LOGIC GOES HERE" sections of the code
	6) Install correctly the two before_save logic hooks methods on Emails
*/

class inboundEmailsLogic
{
	// START - Configuration variables

	// Case status on new email
	public $new_inbound_email_status = 'Email Received';
	
	// Inbound Case team to route from
	public $inbound_cases_team = 'Inbound Cases';
	// Inbound Case team to route to
	public $assignment_cases_team = 'Global';

	// END - Configuration variables


	// before save on emails
        public function reopenCase($bean, $event, $args)
        {
                if(!empty($bean->parent_type) && !empty($bean->parent_id))
                {
			// if the email is related to a case
                        if($bean->parent_type == 'Cases')
                        {
                                $case = BeanFactory::getBean('Cases', $bean->parent_id);

				// if the email is unread and it is related to a case...
                                if($bean->status == 'unread' && !empty($case->id))
                                {

					// START - CUSTOM ASSIGNMENT LOGIC GOES HERE!

					$assignment_team = BeanFactory::getBean('Teams');
					$assignment_team->retrieve_by_string_fields(array('name' => $this->assignment_cases_team));
					if(!empty($assignment_team->id))
					{	
						// assign the inbound email reply to the correct team (who can see the email?)
						$bean->team_id = $bean->team_set_id = $assignment_team->id;
					}

					// END - CUSTOM ASSIGNMENT LOGIC GOES HERE!


					// change the case status, so that the user gets notified (maybe add a workflow?)
					if($case->status != $this->new_inbound_email_status)
					{
		
						$GLOBALS['log']->debug('inboundEmailsLogic->reopenCase - setting case '.$case->id.' to status '.$this->new_inbound_email_status);

                        	                $case->status = $this->new_inbound_email_status;
                                	        $case->save();
					}
				}
                        }
                }
        }

	// before save on emails
	public function customCaseAssignment($bean, $event, $args)
	{
		// if the email is related to a case, it is inbound and unread
		if (!empty($bean->parent_id) && !empty($bean->fetched_row) && $bean->parent_type == 'Cases' && $bean->type == 'inbound' && $bean->status == 'unread' 
			&& $bean->fetched_row['parent_type'] != 'Cases' && $bean->fetched_row['parent_id'] != $bean->parent_id)
		{
			$case = BeanFactory::getBean('Cases', $bean->parent_id);
			
			if(!empty($case->id))
			{
				// find if our default inbound empty team exists...
				$inbound_team = BeanFactory::getBean('Teams');
				$inbound_team->retrieve_by_string_fields(array('name' => $this->inbound_cases_team));

				$assignment_team = BeanFactory::getBean('Teams');
				$assignment_team->retrieve_by_string_fields(array('name' => $this->assignment_cases_team));
				
				if(!empty($inbound_team->id) && !empty($case->team_id) && $inbound_team->id == $case->team_id && !empty($assignment_team->id))
				{
					$GLOBALS['log']->debug('inboundEmailsLogic->customCaseAssignment - Routing appropriately case '.$case->id);

					// START - CUSTOM ASSIGNMENT LOGIC GOES HERE!

					// assign both the email and the case to the correct team (who can see the case/email?)
					$bean->team_id = $bean->team_set_id = $assignment_team->id;
					$case->team_id = $case->team_set_id = $assignment_team->id;

					// now complete the custom case routing...
					if($case->assigned_user_id == '')
					{
						// as an example let's assign the email and the case to a specific user (who needs to handle the case?)
						// let's route to the Sugar default Admin
						$case->assigned_user_id = '1';
						$bean->assigned_user_id = '1';
					}

					// END - CUSTOM ASSIGNMENT LOGIC GOES HERE!


					$GLOBALS['log']->debug('inboundEmailsLogic->customCaseAssignment - Case '.$case->id.' routed');
					$case->save();
				}
			}
		}
	}
}
