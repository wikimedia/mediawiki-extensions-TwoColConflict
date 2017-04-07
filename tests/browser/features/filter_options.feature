@chrome @en.wikipedia.beta.wmflabs.org @firefox @integration
Feature: Two column edit conflict screen
  Background:
    Given I am logged in
    And I have reset my preferences
    And TwoColConflict is enabled as a beta feature

  Scenario: Show only mine filter hides foreign changes
    When I handle a multi line edit conflict
    And I select the show mine option
    Then The two column edit conflict screen should be shown
    And The hide unchanged text option should be selected
    And Section for common changes should be there
    And Section for collapsed common changes should be there
    And Section for full common changes should not be there
    And Section for foreign changes should not be there

  Scenario: Hide common changes filter collapses common changes
    When I handle a multi line edit conflict
    And I select the show unchanged text option
    Then The two column edit conflict screen should be shown
    And Section for full common changes should be there
    And Section for collapsed common changes should not be there

  Scenario: Show hidden common changes when clicking the collapsed text
    When I handle a multi line edit conflict
    And I select the hide unchanged text option
    And I click on the collapsed common changes
    Then The two column edit conflict screen should be shown
    And Section for full common changes should be there
    And Section for collapsed common changes should not be there
    And The show unchanged text option should be selected
