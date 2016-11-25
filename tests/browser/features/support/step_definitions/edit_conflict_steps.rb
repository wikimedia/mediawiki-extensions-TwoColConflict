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
  expect(on(EditConflictPage).twocolconflict_editor_text_element.text).to match(text)
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

Then(/^Section for foreign changes should be there$/) do
  expect(on(EditConflictPage).twocolconflict_changes_foreign_element).to be_visible
end

Then(/^Section for own changes should be there$/) do
  expect(on(EditConflictPage).twocolconflict_changes_own_element).to be_visible
end

Then(/^Section for common changes should not be there$/) do
  expect(on(EditConflictPage).twocolconflict_changes_same_element).not_to be_visible
end

Then(/^Section for foreign changes should not be there$/) do
  expect(on(EditConflictPage).twocolconflict_changes_foreign_element).not_to be_visible
end

Then(/^Section for own changes should not be there$/) do
  expect(on(EditConflictPage).twocolconflict_changes_own_element).not_to be_visible
end

Then(/^Foreign version title should be there$/) do
  expect(on(EditConflictPage).twocolconflict_changes_title_foreign_element).to be_visible
end

Then(/^Own version title should be there$/) do
  expect(on(EditConflictPage).twocolconflict_changes_title_own_element).to be_visible
end
