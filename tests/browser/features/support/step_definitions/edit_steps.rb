When(/^Another user changes content of the "(.+?)" page to "(.+?)"$/) do |page_title, page_content|
  as_user(:conflicting_user) do
    api.edit(
        title: page_title,
        text: page_content,
        summary: 'Conflicting edit'
    )
  end
end

Given(/^I go to the "(.+)" page with multi line content$/) do |page_title|
  api.create_page page_title, "Line1\n\nLine2\n\nLine3\n\nLine4\n\nLine5\n\nLine6\n\nLine7"
  step "I am on the #{page_title} page"
end

Given(/^I go to the "(.+)" page with many lines$/) do |page_title|
  api.create_page page_title, "Line1\n\n\n\nLine3\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\nLastLine"
  step "I am on the #{page_title} page"
end

When(/^Another user changes the multi line content of the "(.+?)" page$/) do |page_title|
  as_user(:conflicting_user) do
    api.edit(
        title: page_title,
        text: "Line1\n\nLine2\n\nLine3ChangeA\n\nLine4\n\nLine5\n\nLine6\n\nLine7ChangeA",
        summary: 'Conflicting edit'
    )
  end
end

When(/^Another user changes some of the many lines of the "(.+?)" page$/) do |page_title|
  as_user(:conflicting_user) do
    api.edit(
        title: page_title,
        text: "Line1\n\n\n\nLine3A\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\nLastLineChangeA",
        summary: 'Conflicting edit'
    )
  end
end
