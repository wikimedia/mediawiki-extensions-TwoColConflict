@chrome @en.wikipedia.beta.wmflabs.org @firefox @integration
Feature: Two column edit conflict screen
  Background:
    Given I am logged in
    And I have reset my preferences
    And TwoColConflict is enabled as a beta feature

  Scenario: Hide common changes filter collapses common changes
    When I handle a multi line edit conflict
    And I have dismissed the help dialog
    And I select the show unchanged text option
    Then The two column edit conflict screen should be shown
    And Section for full common changes should be there
    And Section for collapsed common changes should not be there

  Scenario: Hide common changes when clicking a collapse changes button
    When I handle a multi line edit conflict
    And I have dismissed the help dialog
    And I select the show unchanged text option
    And I click on a collapse changes button
    Then The two column edit conflict screen should be shown
    And Section for full common changes should not be there
    And Section for collapsed common changes should be there
    And The hide unchanged text option should be selected

  Scenario: Show hidden common changes when clicking an expand changes button
    When I handle a multi line edit conflict
    And I have dismissed the help dialog
    And I select the hide unchanged text option
    And I click on an expand changes button
    Then The two column edit conflict screen should be shown
    And Section for full common changes should be there
    And Section for collapsed common changes should not be there
    And The show unchanged text option should be selected