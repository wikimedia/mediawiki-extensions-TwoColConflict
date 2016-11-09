Then(/^The two column edit conflict screen should be shown$/) do
  step 'An explanation header should be shown'
  step 'A description for the changes column should be shown'
  step 'A description for the editor column should be shown'
  step 'A textbox with changes from the user should be shown'
  step 'A textbox for the editor should be shown'
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

Then(/^A textbox with changes from the user should be shown$/) do
  expect(on(EditConflictPage).twocolconflict_changes_text_element).to be_visible
end

Then(/^A textbox for the editor should be shown$/) do
  expect(on(EditConflictPage).twocolconflict_editor_text_element).to be_visible
end

Then(/^The editor should contain "(.+?)"$/) do |text|
  expect(on(EditConflictPage).twocolconflict_editor_text_element.text).to match(text)
end

Then(/^The changes textbox should contain "(.+?)"$/) do |text|
  expect(on(EditConflictPage).twocolconflict_changes_text_element.text).to match(text + @random_string)
end
