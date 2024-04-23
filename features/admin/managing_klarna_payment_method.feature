@managing_klarna_payment_method
Feature: Klarna Payments Payment method validation
  In order to avoid making mistakes when managing a payment method
  As an Administrator
  I want to be prevented from adding it without specifying required fields

  Background:
    Given the store operates on a channel named "US-Store" in "USD" currency
    And I am logged in as an administrator


  @ui
  Scenario: Adding a new klarna payment method with minimal fields
    When I want to create a new Klarna payment method
    And I name it "Klarna" in "English (United States)"
    And I specify its code as "klarna_test"
    And I fill the API username with "Username"
    And I fill the API password with "password"
    And I select SandboxMode
    And make it available in channel "US-Store"
    And I add it
    Then I should be notified that it has been successfully created
    And the payment method "Klarna" should appear in the registry

  @ui
  Scenario: Adding a new klarna payment method with all fields
    When I want to create a new Klarna payment method
    And I name it "Klarna" in "English (United States)"
    And I specify its code as "klarna_test"
    And I fill the API username with "Username"
    And I fill the API password with "password"
    And I select SandboxMode
    And I select "North America" as Klarna account zone
    And I select "pay_now" as Klarna Payment Method
    And make it available in channel "US-Store"
    And I add it
    Then I should be notified that it has been successfully created
    And the payment method "Klarna" should appear in the registry
