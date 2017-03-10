@chrome @en.wikipedia.beta.wmflabs.org @firefox @integration
Feature: Two column edit conflict screen
  Background:
    Given I am logged in
    And I have reset my preferences
    And TwoColConflict is enabled as a beta feature

  Scenario: Basic two-column edit conflict page is shown correctly
    When I go to the "TwoColConflict Test Page" page with many lines
    And I click Edit
    And Another user changes some of the many lines of the "TwoColConflict Test Page" page
    And I edit the page with "ChangeB"
    And I save the edit
    Then The two column edit conflict screen should be shown
    And The editor view should be scrolled
    And The diff view should be scrolled
