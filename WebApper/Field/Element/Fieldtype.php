<?php
namespace PFBC\Element;

class Fieldtype extends Select {
	public function __construct($label, $name, array $properties = null) {
		$options = array(
			'Hidden' => 'Hidden',
			'HTML' => 'HTML',
			'Legend' => 'Legend',
			'Textbox' => 'Textbox',
			'Textarea' => 'Textarea',
			'Select' => 'Select',
			'Checkbox' => 'Checkbox',
			'Radio' => 'Radio',
			'Email' => 'Email',
			'Phone' => 'Phone',
			'Url' => 'Url',
			'jQueryUIDate' => 'jQueryUIDate',
			'Date' => 'Date',
			'Week' => 'Week',
			'Month' => 'Month',
			'Number' => 'Number',
			'Range' => 'Range',
			'Color' => 'Color',
			'Checksort' => 'Checksort',
			'Sort' => 'Sort',
			'CKEditor' => 'CKEditor',
			'State' => 'State',
			'Country' => 'Country',
			'YesNo' => 'YesNo',
			'Captcha' => 'Captcha',
			'Button' => 'Button',
			'UploadFile' => 'File Upload',
			'UploadImage' => 'Image Upload',
			'CollaspeHead' => 'Collaspe Head',
			'CollaspeFoot' => 'Collaspe Foot',
			'CustomField' => 'Custom Field',
			'Fieldset' => 'Fieldset',
			'Action' => 'Action',
			'Equation' => 'Equation',
			'Condition' => 'Condition',
			'Flow' => 'Flow',
			'User' => 'User',
		);
		$value = $properties['value'];
		
		$options = apply_filters( 'web_apper_field_type_options', $options ); // Allow filtering of the field type options;

		parent::__construct($label, $name, $options, $properties);
    }
}