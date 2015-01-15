<?php
class Form {
    private $fields = array();

    public function addField($fieldName, $fieldType, $label, $hint = null, $value = null, $checked = false) {
        $field =  array(
            'name' => $fieldName, 
            'type' => $fieldType
        );

        if(isset($label)) $field['label'] = $label;
        if(isset($hint)) $field['hint'] = $hint;
        if(isset($value)) $field['value'] = $value;
        if(isset($checked)) $field['checked'] = $checked;

        $this->fields[] = $field;
    }

    public function addCheckbox($fieldName, $label, $checked = false) {
        $this->addField($fieldName, 'checkbox', $label, null, null, $checked);
    }

    public function startRadioGroup($label, $name = null, $value = null) {
        $this->fields[] = array('type' => 'radiogrp', 'label' => $label, 'name' => $name, 'value' => $value);
    }

    public function endRadioGroup() {
        $this->fields[] = array('type' => 'endradiogrp');
    }

    public function addRadio($groupName, $value, $label, $checked = false) {
        $this->addField($groupName, 'radio', $label, null, $value, $checked);
    }

    private function mkId($type, $name, $value = null) {
        $value = (isset($value)) ? '-'.Helpers::slugify($value) : '';
        return 'f-'.Helpers::slugify($type).'-'.Helpers::slugify($name).$value;
    }

    public function generate($action, $method = 'POST', $class = null) {
        $output = '<form action="'.$action.'" method="'.$method.'">'.PHP_EOL;

        foreach ($this->fields as $field) {
            if(isset($field['name']))
            $id = $this->mkId($field['type'], $field['name']);

            switch ($field['type']) {
                case 'radiogrp':
                    $for = (isset($field['name'])) ? ' for="'.$this->mkId('radio', $field['name'], $field['value']).'"' : '';
                    if(isset($field['label'])) $output .= '<label'.$for.'>'.$field['label'].'</label> ';
                    break;

                case 'radio':
                    $id .= '-'.Helpers::slugify($field['value']);
                    $checked = ($field['checked']) ? 'checked="checked"' : '';
                    $output .= '<input id="'.$id.'" type="'.$field['type'].'" name="'.$field['name'].'" '.$checked.'value="'.$field['value'].'"/>';
                    if(isset($field['label'])) $output .= '<label for="'.$id.'">'.$field['label'].'</label>';
                    break;

                case 'endradiogrp':
                    $output .= '<br />';
                    break;

                case 'checkbox':
                    $checked = ($field['checked']) ? 'checked="checked"' : '';
                    $placeholder = (isset($field['hint'])) ? 'placeholder="'.$field['hint'].'"' : '';
                    $output .= '<input id="'.$id.'" type="'.$field['type'].'" name="'.$field['name'].'" '.$checked.'/>';
                    if(isset($field['label'])) $output .= '<label for="'.$id.'">'.$field['label'].'</label><br />';
                    break;
                
                default:
                    if(isset($field['label'])) $output .= '<label for="'.$id.'">'.$field['label'].'</label> ';
                    $output .= '<input id="'.$id.'" name="'.$field['name'].'" /><br />';
                    break;
            }

            $output .= PHP_EOL;
        }

        $output .= '</form>';
        return $output;
    }
}/*
require('Helpers.class.php');
$form = new Form();
$form->addField('username', 'text', 'Nom d\'utilisateur', null, 'Username');
$form->addField('password', 'password', 'Mot de passe');
$form->addCheckbox('memorize', 'Se souvenir');
$form->startRadioGroup('Sexe', 'sex', 'male');
$form->addRadio('sex', 'male', 'Homme');
$form->addRadio('sex', 'female', 'Femme');
$form->endRadioGroup();

echo $form->generate('');*/