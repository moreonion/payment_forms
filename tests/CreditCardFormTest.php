<?php

namespace Drupal\payment_forms;

class CreditCardFormTest extends \DrupalUnitTestCase {
  static protected $cards = array(
    'visa_valid' => array(
      'issuer' => 'visa',
      'credit_card_number' => '4444333322221111',
      'secure_code' => '804',
      'expiry_date' => '06/16'
    )
  );

  protected function getValidateMock() {
    $mock = $this->getMock('\Drupal\payment_forms\CreditCardForm', array('formError'));
    return $mock;
  }

  protected function elements() {
    $elements = array();
    foreach (array_keys(self::$cards['visa_valid']) as $key) {
      $elements[$key] = array();
    }
    return $elements;
  }

  function testValidation_validVisa() {
    $form = $this->getValidateMock();
    $form->expects($this->never())->method('formError');
    $data = self::$cards['visa_valid'];
    $form->validateValues($this->elements(), $data);
    $this->assertInstanceOf('DateTime', $data['expiry_date']);
    $this->assertEqual('2016-06', $data['expiry_date']->format('Y-m'));
  }

  function testValidation_validVisaWithWhitespace() {
    $form = $this->getValidateMock();
    $form->expects($this->never())->method('formError');
    $data = self::$cards['visa_valid'];
    $data['credit_card_number'] = '4444 3333 2222 1111';
    $form->validateValues($this->elements(), $data);
    $this->assertEqual('4444333322221111', $data['credit_card_number']);
  }

  function testValidation_invalidCardNumber_formError() {
    $form = $this->getValidateMock();
    $form->expects($this->once())->method('formError');
    $data = self::$cards['visa_valid'];
    $data['credit_card_number'] = '55553333222211X1';
    $form->validateValues($this->elements(), $data);
  }

  function testValidation_invalidSecureCode_formError() {
    $form = $this->getValidateMock();
    $form->expects($this->once())->method('formError');
    $data = self::$cards['visa_valid'];
    $data['secure_code'] = 'XYZ';
    $form->validateValues($this->elements(), $data);
  }

  function testValidation_weirdDate_accepted() {
    $form = $this->getValidateMock();
    $form->expects($this->never())->method('formError');
    $data = self::$cards['visa_valid'];
    $data['expiry_date'] = '14/25';
    $form->validateValues($this->elements(), $data);
    $this->assertEqual('2026-02', $data['expiry_date']->format('Y-m'));
  }

  function testValidation_invalidExpiry_formError() {
    $form = $this->getValidateMock();
    $form->expects($this->once())->method('formError');
    $data = self::$cards['visa_valid'];
    $data['expiry_date'] = '1/2/3';
    $form->validateValues($this->elements(), $data);
  }

  function testValidation_emptyFields_formError() {
    $form = $this->getValidateMock();
    $form->expects($this->exactly(3))->method('formError');
    $data = self::$cards['visa_valid'];
    $data['credit_card_number'] = '';
    $data['secure_code'] = '';
    $data['expiry_date'] = '';
    $form->validateValues($this->elements(), $data);
  }
}
