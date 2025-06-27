# Formie Integration Triggers

Allow waiting for specific events before triggering Formie integrations

## Requirements

This plugin requires Craft CMS 4.3.5 or later, and PHP 8.0.2 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Formie Integration Triggers”. Then press “Install”.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require onstuimig/craft-formie-integration-triggers

# tell Craft to install the plugin
./craft plugin/install formie-integration-triggers
```

## Usage

### Integrations

Integrations have to be modified to be made compatible with triggers.

The integration class needs to extend the trigger version of the base integration class, for example
```php
use verbb\formie\base\Miscellaneous
```
should become
```php
use onstuimig\formieintegrationtriggers\base\formie\Miscellaneous
```

Instead of the `getFormSettingsHtml` method, `getIntegrationFormSettingsHtml` should be used.

```php
public function getIntegrationFormSettingsHtml($form): string
{
	return Craft::$app->getView()->renderTemplate('example-plugin/formie/_form-settings', [
		'integration' => $this,
		'form' => $form,
	]);
}
```

### Triggers

#### Create custom trigger

Create a class extending the `EventTrigger` class that implements the `displayName` and `setup` methods.

When ready to run the integration, call the `sendPayload` method.

```php
use onstuimig\formieintegrationtriggers\base\EventTrigger;

class ExampleTrigger extends EventTrigger
{
	public function displayName(): string
	{
		return 'Example Trigger';
	}

	public function setup(): void
	{
		Event::on(
			Example::class,
			Example::EVENT_EXAMPLE,
			function ($event) {
				// Get the submission related to the event
				$submission = Submission::findOne($event->submissionId);
				
				// Send the integration payload
				$this->sendPayload($submission);
			}
		)
	}
}
```

Optionally extra field options can be presented to the integration by providing a `getFields` method.

```php
public function getFields(): array
{
	/**
	 * Return an array in the format
	 * [ 'handle' => 'Label' ]
	 */
	return [
		'exampleId' => 'Example ID',
		'exampleField' => 'Exampple Field'
	];
}
```

The data for these fields can be provided when calling the `sendPayload` method.

```php
$this->sendPayload($submission, [
	'exampleId' => 12345,
	'exampleField' => 'Example data'
]);
```

#### Register trigger

The trigger can be registered using the `Triggers::EVENT_REGISTER_TRIGGERS` event.

```php
use onstuimig\formieintegrationtriggers\services\Triggers;
use onstuimig\formieintegrationtriggers\events\RegisterTriggersEvent;

Event::on(
	Triggers::class,
	Triggers::EVENT_REGISTER_TRIGGERS,
	function(RegisterTriggersEvent $event) {
		$event->triggers[] = ExampleTrigger::class;
	}
);
```
