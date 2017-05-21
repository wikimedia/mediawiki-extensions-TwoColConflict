Given(/^I go to the "(.+)" page with content "(.+)"$/) do |page_title, page_content|
  @wikitext = page_content
  api.create_page page_title, page_content
  step "I am on the #{page_title} page"
end

Given(/^I am on the (.+) page$/) do |article|
  article = article.gsub(/ /, '_')
  visit(ZtargetPage, using_params: { article_name: article })
end
