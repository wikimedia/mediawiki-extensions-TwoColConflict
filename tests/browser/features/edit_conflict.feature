@chrome @en.wikipedia.beta.wmflabs.org @firefox @integration
Feature: Two column edit conflict screen
  Scenario: Basic two-column edit conflict page is shown correctly
    When I go to the "TwoColConflict Test Page" page with content "I am a sentence."
    And I click Edit
    And Another user changes content of the "TwoColConflict Test Page" page to "I am a longer sentence than before."
    And I edit the page with "Adding some random content."
    And I save the edit
    Then The two column edit conflict screen should be shown
    And The editor should contain "I am a longer sentence than before."
    And The changes textbox should contain "Adding some random content."
