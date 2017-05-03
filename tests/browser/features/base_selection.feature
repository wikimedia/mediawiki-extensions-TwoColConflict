@chrome @en.wikipedia.beta.wmflabs.org @firefox @integration
Feature: Two column edit conflict screen
  Background:
    Given I am logged in
    And I have reset my preferences
    And TwoColConflict is enabled as a beta feature

  Scenario: The base selection dialog shows with the correct preselection
    When I handle an edit conflict
    And I have dismissed the help dialog
    Then The base version selections screen should show
    And The use currently published version option should be selected

  Scenario: Using the currently published version in the selection dialog shows that version
    When I go to the "TwoColConflict Test Page" page with content "I am a sentence."
    And I click Edit
    And Another user changes content of the "TwoColConflict Test Page" page to "I am a longer sentence than before."
    And I edit the page with "Adding some random content."
    And I save the edit
    And I have dismissed the help dialog
    And I click the ok button in the base selection dialog
    Then The base version selections screen should hide
    And The editor should contain "I am a longer sentence than before."

  Scenario: Using my text in the selection dialog shows that version
    When I go to the "TwoColConflict Test Page" page with content "I am a sentence."
    And I click Edit
    And Another user changes content of the "TwoColConflict Test Page" page to "I am a longer sentence than before."
    And I edit the page with "Adding some random content."
    And I save the edit
    And I have dismissed the help dialog
    And I select the my text option in the base selection dialog
    And I click the ok button in the base selection dialog
    Then The base version selections screen should hide
    And The editor should contain "Adding some random content."
