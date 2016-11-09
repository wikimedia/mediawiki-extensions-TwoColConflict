When(/^Another user changes content of the "(.+?)" page to "(.+?)"$/) do |page_title, page_content|
  as_user(:conflicting_user) do
    api.edit(
        title: page_title,
        text: page_content,
        summary: "Conflicting edit"
    )
  end
end
