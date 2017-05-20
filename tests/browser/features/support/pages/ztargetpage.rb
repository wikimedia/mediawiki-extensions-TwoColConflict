class ZtargetPage < MainPage
  include PageObject

  page_url '<%=params[:article_name]%>'
end
