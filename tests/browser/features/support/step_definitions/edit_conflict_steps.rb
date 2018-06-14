Then(/^The two column edit conflict screen should be shown$/) do
  step 'An explanation header should be shown'
  step 'A description for the changes column should be shown'
  step 'A description for the editor column should be shown'
  step 'A textbox with conflicting changes should be shown'
  step 'A textbox for the editor should be shown'
end

Then(/^Changes should be shown as split into foreign and own$/) do
  step 'Foreign version title should be there'
  step 'Own version title should be there'
  step 'Section for foreign changes should be there'
  step 'Section for own changes should be there'
end

Then(/^An explanation header should be shown$/) do
  expect(on(EditConflictPage).twocolconflict_explanation_header_element).to be_visible
end

Then(/^A description for the changes column should be shown$/) do
  expect(on(EditConflictPage).twocolconflict_changes_desc_element).to be_visible
end

Then(/^A description for the editor column should be shown$/) do
  expect(on(EditConflictPage).twocolconflict_editor_desc_element).to be_visible
end

Then(/^A textbox with conflicting changes should be shown$/) do
  expect(on(EditConflictPage).twocolconflict_changes_text_element).to be_visible
end

Then(/^A textbox for the editor should be shown$/) do
  expect(on(EditConflictPage).twocolconflict_editor_text_element).to be_visible
end

Then(/^The editor should contain "(.+?)"$/) do |text|
  expect(on(EditConflictPage).twocolconflict_editor_text_element.value).to match(text)
end

Then(/^Own changes section should contain "(.+?)"$/) do |text|
  expect(on(EditConflictPage).twocolconflict_changes_own_element.text).to match(text + @random_string)
end

Then(/^Foreign changes section should contain "(.+?)"$/) do |text|
  expect(on(EditConflictPage).twocolconflict_changes_foreign_element.text).to match(text)
end

Then(/^Section for common changes should be there$/) do
  expect(on(EditConflictPage).twocolconflict_changes_same_element).to be_visible
end

Then(/^Section for full common changes should be there$/) do
  expect(on(EditConflictPage).twocolconflict_changes_same_full_element).to be_visible
end

Then(/^Section for collapsed common changes should be there$/) do
  expect(on(EditConflictPage).twocolconflict_changes_same_collapsed_element).to be_visible
end

Then(/^Section for foreign changes should be there$/) do
  expect(on(EditConflictPage).twocolconflict_changes_foreign_element).to be_visible
end

Then(/^Section for own changes should be there$/) do
  expect(on(EditConflictPage).twocolconflict_changes_own_element).to be_visible
end

Then(/^Section for common changes should not be there$/) do
  expect(on(EditConflictPage).twocolconflict_changes_same_element).not_to be_present
end

Then(/^Section for full common changes should not be there$/) do
  expect(on(EditConflictPage).twocolconflict_changes_same_full_element).not_to be_present
end

Then(/^Section for collapsed common changes should not be there$/) do
  expect(on(EditConflictPage).twocolconflict_changes_same_collapsed_element).not_to be_present
end

Then(/^Foreign version title should be there$/) do
  expect(on(EditConflictPage).twocolconflict_changes_title_foreign_element).to be_visible
end

Then(/^Own version title should be there$/) do
  expect(on(EditConflictPage).twocolconflict_changes_title_own_element).to be_visible
end

When(/^I select the hide unchanged text option$/) do
  on(EditConflictPage).twocolconflict_option_hide_div_element.when_present.click
end

When(/^I select the show unchanged text option$/) do
  on(EditConflictPage).twocolconflict_option_show_div_element.when_present.click
end

When(/^I handle an edit conflict$/) do
  step 'I go to the "TwoColConflict Test Page" page with content "I am a sentence."'
  step 'I click Edit'
  step 'Another user changes content of the "TwoColConflict Test Page" page to "I am a longer sentence than before."'
  step 'I edit the page with "Adding some random content."'
  step 'I save the edit'
end

When(/^I handle a multi line edit conflict$/) do
  step 'I go to the "TwoColConflict Test Page" page with multi line content'
  step 'I click Edit'
  step 'Another user changes the multi line content of the "TwoColConflict Test Page" page'
  step 'I edit the page with "ChangeB"'
  step 'I save the edit'
end

When(/^I click on a collapse changes button$/) do
  on(EditConflictPage).twocolconflict_collapse_changes_element.when_present.click
end

When(/^I click on an expand changes button$/) do
  on(EditConflictPage).twocolconflict_expand_changes_element.when_present.click
end

Then(/^The show unchanged text option should be selected$/) do
  expect(on(EditConflictPage).twocolconflict_option_show_selected?).to be_truthy
end

Then(/^The hide unchanged text option should be selected$/) do
  expect(on(EditConflictPage).twocolconflict_option_hide_selected?).to be_truthy
end

Then(/^The editor view should be scrolled$/) do
  browser.execute_script('$( ".mw-twocolconflict-editor-col textarea" ).scrollTop() > 0;')
end

Then(/^The diff view should be scrolled$/) do
  browser.execute_script('$( ".mw-twocolconflict-changes-editor" ).scrollTop() > 0;')
end
