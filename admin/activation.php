<?php

	/**
	 * Contains activation hook for the StyleRoller.
	 *
	 * @author Paul Kashtanoff <paul@byonepress.com>
	 * @author Alex Kovalev <alex@byonepress.com>
	 * @copyright (c) 2014, OnePress Ltd
	 *
	 * @package styleroller
	 * @since 1.0.0
	 */
	class OnpSL_Styler_Activation extends Factory000_Activator {

		public function activate()
		{

			$this->plugin->license->setDefaultLicense(array(
				'Category' => 'free',
				'Build' => 'premium',
				'Title' => 'OnePress Zero License',
				'Description' => __('Please, activate the plugin to get started. Enter a key
									you received with the plugin into the form below.', 'styleroller')
			));
		}
	}

	$styleroller->registerActivation('OnpSL_Styler_Activation');