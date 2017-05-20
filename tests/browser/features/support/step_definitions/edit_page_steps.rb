When(/^I click Edit$/) do
  on(MainPage).edit_link
end

When(/^I edit the page with "(.*?)"$/) do |edit_content|
  on(EditPage).edit_page_content_element.send_keys(edit_content + @random_string)
end

When(/^I save the edit$/) do
  on(EditPage).save_button
end
