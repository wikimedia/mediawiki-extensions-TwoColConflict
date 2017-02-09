class SpecialPreferencesPage
  include PageObject
  page_url 'Special:Preferences'

  a(:beta_features_tab, css: '#preftab-betafeatures')
  text_field(:twocolconflict_checkbox, css: '[name=wptwocolconflict]')
  button(:submit_button, css: '#prefcontrol')

  def enable_twocolconflict
    beta_features_tab_element.when_present.click
    return unless twocolconflict_checkbox_element.attribute('checked').nil?
    twocolconflict_checkbox_element.click
    submit_button_element.when_present.click
  end

  def disable_twocolconflict
    beta_features_tab_element.when_present.click
    return if twocolconflict_checkbox_element.attribute('checked').nil?
    twocolconflict_checkbox_element.click
    submit_button_element.when_present.click
  end
end