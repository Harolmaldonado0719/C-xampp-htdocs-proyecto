<?php

class Validator {
    private $errors = [];
    private $currentFieldForError; 

    
    public function validate($value, $fieldName, array $rules) {
        $this->currentFieldForError = $fieldName; // Guardar el nombre del campo actual

        foreach ($rules as $ruleData) {
            $ruleName = $ruleData;
            $param = null;

            // Comprobar si la regla es un array (para reglas con parámetros pasados como array)
            // Ejemplo: ['max_val', 99999]
            if (is_array($ruleData)) { 
                $ruleName = $ruleData[0];
                $param = $ruleData[1] ?? null;
            } 
            // Comprobar si la regla tiene un formato 'nombre_regla:parametro'
            // Ejemplo: 'max:100'
            elseif (is_string($ruleData) && strpos($ruleData, ':') !== false) { 
                list($ruleName, $param) = explode(':', $ruleData, 2);
            }
            // Si es solo una cadena, es una regla sin parámetros. Ejemplo: 'required'
            // $ruleName ya tiene el valor correcto.

            // Construir el nombre del método de validación
            // Convierte 'min_val' a 'validateMinVal', 'required' a 'validateRequired'
            $methodNameParts = explode('_', $ruleName);
            $methodNameParts = array_map('ucfirst', $methodNameParts);
            $methodName = 'validate' . implode('', $methodNameParts);

            if (method_exists($this, $methodName)) {
                if (!$this->$methodName($value, $param)) {
                    // El error se añade directamente en el método de validación específico (ej: $this->validateRequired())
                }
            } else {
                 // Registrar un error si la regla de validación no tiene un método correspondiente
                 error_log("Validator: Regla de validación desconocida '{$ruleName}' (método esperado '{$methodName}') para el campo '{$fieldName}'.");
                 $this->addError($fieldName, "Regla de validación interna desconocida: {$ruleName}.");
            }
        }
        unset($this->currentFieldForError); // Limpiar el campo actual después de validar todas sus reglas
        
        // Devuelve true si no hay errores para este campo específico, false si los hay.
        return !isset($this->errors[$fieldName]); 
    }

    public function addError($field, $message) {
        $this->errors[$field][] = $message;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function hasErrors() {
        return !empty($this->errors);
    }

    // --- Métodos de Validación Específicos ---
    // Cada uno debe llamar a $this->addError($this->getCurrentField(), "mensaje"); si la validación falla.

    protected function validateRequired($value, $param = null) {
        if ($value === null || trim((string)$value) === '') {
            $this->addError($this->getCurrentField(), 'Este campo es obligatorio.');
            return false;
        }
        return true;
    }

    protected function validateEmail($value, $param = null) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($this->getCurrentField(), 'El formato del correo electrónico no es válido.');
            return false;
        }
        return true;
    }

    protected function validateMax($value, $param) { // Para longitud de cadena
        if (mb_strlen((string)$value) > (int)$param) { // Usar mb_strlen para caracteres multibyte
            $this->addError($this->getCurrentField(), "Este campo no debe exceder los {$param} caracteres.");
            return false;
        }
        return true;
    }
    
    protected function validateMin($value, $param) { // Para longitud de cadena
        if (mb_strlen((string)$value) < (int)$param) { // Usar mb_strlen
            $this->addError($this->getCurrentField(), "Este campo debe tener al menos {$param} caracteres.");
            return false;
        }
        return true;
    }

    protected function validateNumeric($value, $param = null) {
        if (!is_numeric($value)) {
            $this->addError($this->getCurrentField(), 'Este campo debe ser numérico.');
            return false;
        }
        return true;
    }

    protected function validateInteger($value, $param = null) {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            // Comprobar también si es un string numérico que representa un entero, ya que FILTER_VALIDATE_INT es estricto
            if (!is_numeric($value) || floor((float)$value) != (float)$value) {
                 $this->addError($this->getCurrentField(), 'Este campo debe ser un número entero.');
                 return false;
            }
        }
        return true;
    }
    
    protected function validateMinVal($value, $param) { // Para valores numéricos mínimos
        if (!is_numeric($value) || (float)$value < (float)$param) {
            $this->addError($this->getCurrentField(), "El valor debe ser al menos {$param}.");
            return false;
        }
        return true;
    }

    protected function validateMaxVal($value, $param) { // Para valores numéricos máximos
        if (!is_numeric($value) || (float)$value > (float)$param) {
            $this->addError($this->getCurrentField(), "El valor no debe exceder {$param}.");
            return false;
        }
        return true;
    }
    
    // Método auxiliar para obtener el nombre del campo actual para el que se está validando una regla.
    private function getCurrentField() {
        return $this->currentFieldForError ?? 'general'; // 'general' como fallback si no está seteado
    }
}
?>