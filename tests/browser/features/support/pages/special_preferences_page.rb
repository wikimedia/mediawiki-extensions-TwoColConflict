class SpecialPreferencesPage
  include PageObject
  page_url 'Special:Preferences'

  link(:beta_features_tab, css: '#preferences .oo-ui-tabOptionWidget:nth-of-type(6) span')
  checkbox(:twocolconflict_checkbox, name: 'wptwocolconflict')
  span(:twocolconflict_checkbox_div, xpath: '//*[@name="wptwocolconflict"]//parent::span')
  button(:submit_button, css: '#prefcontrol button')

  def enable_twocolconflict
    beta_features_tab_element.when_visible.click
    return if twocolconflict_checkbox_checked?
    twocolconflict_checkbox_div_element.click
    submit_button_element.when_enabled.click
  end

  def disable_twocolconflict
    beta_features_tab_element.when_visible.click
    return unless twocolconflict_checkbox_checked?
    twocolconflict_checkbox_div_element.click
    submit_button_element.when_enabled.click
  end
end
