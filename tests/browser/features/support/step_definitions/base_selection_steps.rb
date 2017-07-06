Then(/^The base version selections screen should show$/) do
  expect(on(EditConflictPage).twocolconflict_base_dialog_element.when_present).to be_visible
end

Then(/^The base version selections screen should hide/) do
  expect(on(EditConflictPage).twocolconflict_base_dialog_element.when_not_present).not_to be_present
end

Then(/^The use currently published version option should be selected$/) do
  expect(on(EditConflictPage).twocolconflict_base_option_element.value).to match('current')
end

When(/^I click the ok button in the base selection dialog$/) do
  on(EditConflictPage).twocolconflict_base_submit_element.when_visible.click
end

When(/^I select the my text option in the base selection dialog$/) do
  on(EditConflictPage).twocolconflict_base_mine_label_element.when_visible.click
end
