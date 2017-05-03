When(/^I click on the show help button$/) do
  on(EditConflictPage).twocolconflict_show_help_element.when_present.click
end

When(/^I have dismissed the help dialog$/) do
  step 'I have closed the help dialog at the start'
  step 'I wait until help dialog is hidden'
end

When(/^I have closed the help dialog at the start$/) do
  on(EditConflictPage).twocolconflict_help_close_start_element.when_present.click
end

When(/^I have closed the help dialog at the end/) do
  on(EditConflictPage).twocolconflict_help_close_end_element.when_present.click
end

When(/^I have moved to the next step$/) do
  on(EditConflictPage).twocolconflict_help_next_element.when_present.click
end

When(/^I wait until help dialog is hidden$/) do
  step 'The help dialog is hidden'
end

Given(/^The help dialog is hidden$/) do
  on(EditConflictPage).wait_for_help_dialog_to_hide
end

When(/^The help dialog is visible$/) do
  step 'The help dialog should be visible'
end

Then(/^The help dialog should be visible/) do
  expect(on(EditConflictPage).twocolconflict_help_dialog_element.when_present).to be_visible
end

Then(/^The help dialog should not be present/) do
  expect(on(EditConflictPage).twocolconflict_help_dialog_element.when_not_present).not_to be_present
end
