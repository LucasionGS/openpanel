<?php
namespace OpenPanel\core\webhost;

class HostAddonForm {
  private array $fields;

  /**
   * Add a new input field to the form.
   * @param string $name The name of the input field. This will be used as the `key` in the $_POST and used as the $addons[`key`].
   * @param string $label Label displayed above the input field.
   * @param string $type The type of input field. Default is "text".
   * @param string $value The default value of the input field.
   * @param array $options Additional options for the input field. Different input types may have different options.
   */
  public function addInput(string $name, string $label, string $type = "text", string $value = "", array $options = []) {
    $this->fields[] = [
      "name" => $name,
      "label" => $label,
      "type" => $type,
      "value" => $value,
      "options" => $options
    ];
  }

  /**
   * Add a new select field to the form.
   * @param string $name The name of the select field. This will be used as the `key` in the $_POST and used as the $addons[`key`].
   * @param string $label Label displayed above the select field.
   * @param array $options An array of options for the select field. The key is the value of the option, and the value is the text displayed.
   * @param string $value The default value of the select field.
   */
  public function addSelect(string $name, string $label, array $options, string $value = "") {
    $this->fields[] = [
      "name" => $name,
      "label" => $label,
      "type" => "select",
      "options" => $options,
      "value" => $value
    ];
  }


  public function render($addonValues= []) {
    foreach ($this->fields as $field) {
      echo "<div>";
      echo "<label for='addon_{$field['name']}'>{$field['label']}</label>";
      echo "<br>";
      if ($field['type'] === "select") {
        echo "<select name='addon_{$field['name']}'>";
        foreach ($field['options'] as $key => $value) {
          echo "<option value='$key' " . (($addonValues[$key] ?? $field['value']) === $key ? "selected" : "") . ">$value</option>";
        }
        echo "</select>";
      } else {
        echo "<input type='{$field['type']}' name='addon_{$field['name']}' value='{$field['value']}'>";
      }
      echo "</div>";
    }
  }
}