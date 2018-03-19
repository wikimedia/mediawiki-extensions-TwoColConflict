@chrome @en.wikipedia.beta.wmflabs.org @firefox @integration
Feature: Two column edit conflict help
  Background:
    Given I am logged in
    And I have reset my preferences
    And TwoColConflict is enabled as a beta feature
    And I handle an edit conflict

  Scenario: Two column edit conflict tutorial is present on first load
    Then The help dialog should be visible

  Scenario: Two column edit conflict tutorial is not present after it was dismissed once
    When I have dismissed the help dialog
    And I refresh the edit conflict page
    And I dismiss the refresh dialogs
    Then The help dialog should not be present

  Scenario: Two column edit conflict sequence works
    When The help dialog is visible
    And I have moved to the next step
    And I have moved to the next step
    And I have moved to the next step
    And I have moved to the next step
    And I have closed the help dialog at the end
    Then The help dialog should not be present
