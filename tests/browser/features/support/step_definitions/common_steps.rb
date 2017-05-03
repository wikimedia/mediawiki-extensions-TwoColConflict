Then(/^TwoColConflict is enabled as a beta feature$/) do
  visit(SpecialPreferencesPage).enable_twocolconflict
end

Then(/^TwoColConflict is disabled as a beta feature$/) do
  visit(SpecialPreferencesPage).disable_twocolconflict
end

Given(/^I refresh the edit conflict page$/) do
  on(EditConflictPage) do |page|
    page.refresh
  end
end

And(/^I dismiss the refresh dialogs$/) do
  browser.alert.ok
end
