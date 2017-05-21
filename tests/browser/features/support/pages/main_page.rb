class MainPage
  include PageObject

  page_url ''

  a(:edit_link, css: '#ca-edit a')
end
