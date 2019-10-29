<?php

namespace ProcessWire\GraphQL\Test\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use ProcessWire\GraphQL\Schema;
use ProcessWire\GraphQL\Utils;

class SchemaFieldExists extends Constraint {

  const introspectionQuery = "query IntrospectionQuery {
    __schema {
      queryType {
        name
      }
      mutationType {
        name
      }
      subscriptionType {
        name
      }
      types {
        ...FullType
      }
      directives {
        name
        description
        locations
        args {
          ...InputValue
        }
      }
    }
  }
  
  fragment FullType on __Type {
    kind
    name
    description
    fields(includeDeprecated: true) {
      name
      description
      args {
        ...InputValue
      }
      type {
        ...TypeRef
      }
      isDeprecated
      deprecationReason
    }
    inputFields {
      ...InputValue
    }
    interfaces {
      ...TypeRef
    }
    enumValues(includeDeprecated: true) {
      name
      description
      isDeprecated
      deprecationReason
    }
    possibleTypes {
      ...TypeRef
    }
  }
  
  fragment InputValue on __InputValue {
    name
    description
    type {
      ...TypeRef
    }
    defaultValue
  }
  
  fragment TypeRef on __Type {
    kind
    name
    ofType {
      kind
      name
      ofType {
        kind
        name
        ofType {
          kind
          name
          ofType {
            kind
            name
            ofType {
              kind
              name
              ofType {
                kind
                name
                ofType {
                  kind
                  name
                }
              }
            }
          }
        }
      }
    }
  }
  ";

  public function matches($other): bool
  {
    self::ensureProperArgument($other);
    $field = self::selectSchemaField($other);
    return !is_null($field);
  }

  public static function selectByProperty($arr, $property, $value)
  {
    foreach ($arr as $item) {
      if ($item->$property === $value) {
        return $item;
      }
    }
    return null;
  }

  public static function getIntrospection()
  {
    Schema::build();
    $res = Utils::module()->executeGraphQL(self::introspectionQuery);
    return json_decode(json_encode($res), false);
  }

  public static function selectSchemaField(array $path)
  {
    $introspection = self::getIntrospection();
    $types = $introspection->data->__schema->types;
    return self::traverseSchemaField($types, ucfirst($path[0]), array_slice($path, 1));
  }

  private static function traverseSchemaField($types, $typeName, $path)
  {
    // select the type
    $type = self::selectByProperty($types, 'name', $typeName);

    // if there is no path then user wants to select root operation
    if (!count($path)) {
      return $type;
    }

    // select the field
    $fieldName = $path[0];
    $field = self::selectByProperty($type->fields, 'name', $fieldName);

    // if there is no more field names to traverse then return what we got
    if (count($path) === 1) {
      return $field;
    }

    // if the field is null then return what we got
    if (is_null($field)) {
      return $field;
    }

    // get the type of the field
    $type = $field->type;
    if (in_array($type->kind, ['LIST', 'NON_NULL'])) {
      $type = $type->ofType;
    }

    // if the field is scalar and we still got field names to traverse through
    // then it cannot be found
    if ($type->kind === 'SCALAR') {
      return null;
    }

    return self::traverseSchemaField($types, $type->name, array_slice($path, 1));
  }

  
  public function toString(): string
  {
    return 'field path exists';
  }

  /**
   * Return additional failure description where needed
   *
   * The function can be overridden to provide additional failure
   * information like a diff
   *
   * @param mixed $other evaluated value or object
   */
  protected function additionalFailureDescription($other): string
  {
    $rootOperations = ['query', 'mutation'];
    if (!in_array($other[0], $rootOperations)) {
      return 'Please make sure your path starts with one of the root operations: ' . implode(', ', $rootOperations);
    }
    return '';
  }

  /**
   * Returns the description of the failure
   *
   * The beginning of failure messages is "Failed asserting that" in most
   * cases. This method should return the second part of that sentence.
   *
   * To provide additional failure information additionalFailureDescription
   * can be used.
   *
   * @param mixed $other evaluated value or object
   *
   * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
   */
  protected function failureDescription($other): string
  {
    $desc = $this->exporter->export($other);
    if (is_array($other)) {
      $desc = implode('.', $other);
      $desc = "\"$desc\"";
    }
    return $desc . ' ' . $this->toString();
  }

  protected static function ensureProperArgument($other)
  {
    $rootOperations = ['query', 'mutation'];
    if (!in_array($other[0], $rootOperations)) {
      $msg = 'Please make sure your path starts with one of the root operations: "' . implode('", "', $rootOperations) . '".';
      $msg .= "\n";
      $msg .= 'Your path is: ' . json_encode($other);
      throw new ExpectationFailedException($msg);
    }
  }
}