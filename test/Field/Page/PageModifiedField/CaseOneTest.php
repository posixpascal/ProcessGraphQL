<?php

namespace ProcessWire\GraphQL\Test\Field\Page\Fieldtype;

/**
 * Returns the correct default value.
 */

use \ProcessWire\GraphQL\Utils;
use \ProcessWire\GraphQL\Test\GraphQLTestCase;

class PageModifiedFieldCaseOneTest extends GraphQLTestCase {

  const settings = [
    'login' => 'admin',
    'legalTemplates' => ['skyscraper'],
    'legalPageFields' => ['modified'],
  ];

	
  public function testValue()
  {
  	$skyscraper = Utils::pages()->get("template=skyscraper");
  	$query = "{
  		skyscraper (s: \"id=$skyscraper->id\") {
  			list {
  				modified
  			}
  		}
  	}";
  	$res = self::execute($query);
  	assertEquals(
      $skyscraper->modified,
      $res->data->skyscraper->list[0]->modified,
      'Retrieves correct default value of `modified` field of the page.'
    );
    assertObjectNotHasAttribute('errors', $res, 'There are errors.');
  }

}