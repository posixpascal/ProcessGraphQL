<?php namespace ProcessWire\GraphQL\Type\Fieldtype;

use GraphQL\Type\Definition\CustomScalarType;
use ProcessWire\GraphQL\Type\Traits\CacheTrait;
use ProcessWire\GraphQL\Type\Traits\FieldTrait;
use ProcessWire\GraphQL\Type\Traits\InputFieldTrait;
use ProcessWire\GraphQL\Type\Traits\SetValueTrait;

class FieldtypeEmail
{ 
  use CacheTrait;
  use FieldTrait;
  use InputFieldTrait;
  use SetValueTrait;
  public static function type($field)
  {
    return self::cache($field->name, function () {
      return new CustomScalarType([
        'name' => 'Email',
        'description' => 'E-Mail address in valid format',
        'serialize' => function ($value) {
          return (string) $value;
        },
        'parseValue' => function ($value) {
          return (string) $value;
        },
        'parseLiteral' => function ($valueNode) {
          return (string) $valueNode->value;
        },
      ]);
    });
  }
}
