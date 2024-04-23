@paying_with_klarna_for_order
Feature: Payment change are taken into acount
  In order to enable the customer to change payment method
  As a site
  I don't want to have any error

  Background:
    Given the store operates on a channel named "DE-Store" in "EUR" currency
    And there is a zone "The Rest of the World" containing all other countries
    And there is a user "louise.ottopeters@test.de" identified by "password123"
    And the store has a payment method "Klarna" with a code "klarna" and Klarna payment PAY LATER gateway
    And the store has also a payment method "Cash" with a code "Cash"
    And the payment method "Klarna" requires authorization before capturing
    And the store has a product "Dronehop Mug" priced at "€20.00"
    And the store ships to "Germany"
    And the store ships everywhere for free
    And I am logged in as "louise.ottopeters@test.de"

  @ui
  Scenario: Successfully changing payment
    Given I have product "Dronehop Mug" in the cart
    And I am at the checkout addressing step
    When I specify the billing address as "Leipzig", "Arthur-Hoffmann-Straße 110", "04275", "Germany" for "Louise Otto-Peters"
    And I complete the addressing step
    And I select "Free" shipping method
    And I complete the shipping step and klarna session could be initialized
    Then I should be able to select "Klarna" payment method
    And I complete the payment step
    When I go back to payment step of the checkout
    And I select "Cash" payment method
    Then I complete the payment step
