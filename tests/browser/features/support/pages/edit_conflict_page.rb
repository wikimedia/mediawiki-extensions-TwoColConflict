class EditConflictPage
  include PageObject

  div(:twocolconflict_explanation_header, class: 'mw-twocolconflict-explainconflict')
  div(:twocolconflict_changes_desc, css: '.mw-twocolconflict-changes-col .mw-twocolconflict-col-desc')
  div(:twocolconflict_changes_text, id: 'mw-twocolconflict-changes-editor')
  div(:twocolconflict_editor_desc, css: '.mw-twocolconflict-editor-col .mw-twocolconflict-col-desc')
  text_area(:twocolconflict_editor_text, css: '.mw-twocolconflict-editor-col textarea')
  div(:twocolconflict_changes_same, css: '#mw-twocolconflict-changes-editor .mw-twocolconflict-diffchange-same')
  div(
      :twocolconflict_changes_same_full,
      css: '#mw-twocolconflict-changes-editor .mw-twocolconflict-diffchange-same-full'
  )
  div(
      :twocolconflict_changes_same_collapsed,
      css: '#mw-twocolconflict-changes-editor .mw-twocolconflict-diffchange-same-collapsed'
  )
  div(:twocolconflict_changes_foreign, css: '#mw-twocolconflict-changes-editor .mw-twocolconflict-diffchange-foreign')
  div(:twocolconflict_changes_own, css: '#mw-twocolconflict-changes-editor .mw-twocolconflict-diffchange-own')
  div(
      :twocolconflict_changes_title_foreign,
      css: '#mw-twocolconflict-changes-editor .mw-twocolconflict-diffchange-foreign .mw-twocolconflict-diffchange-title'
  )
  div(:twocolconflict_changes_title_own,
      css: '#mw-twocolconflict-changes-editor .mw-twocolconflict-diffchange-own .mw-twocolconflict-diffchange-title'
  )

  span(:twocolconflict_option_both_div, xpath: '(//*[@name="mw-twocolconflict-show-changes"]//parent::span)[1]')
  span(:twocolconflict_option_mine_div, xpath: '(//*[@name="mw-twocolconflict-show-changes"]//parent::span)[2]')

  radio_button(:twocolconflict_option_show, xpath: '(//*[@name="mw-twocolconflict-same"])[1]')
  radio_button(:twocolconflict_option_hide, xpath: '(//*[@name="mw-twocolconflict-same"])[2]')
  span(:twocolconflict_option_show_div, xpath: '(//*[@name="mw-twocolconflict-same"]//parent::span)[1]')
  span(:twocolconflict_option_hide_div, xpath: '(//*[@name="mw-twocolconflict-same"]//parent::span)[2]')

  button(:twocolconflict_show_help, css: '.mw-twocolconflict-show-help > button')
  div(:twocolconflict_help_dialog, css: '.mw-twocolconflict-help-dialog')
  link(:twocolconflict_help_next, css: '.mw-twocolconflict-help-next > a')
  link(:twocolconflict_help_previous, css: '.mw-twocolconflict-help-previous > a')
  link(:twocolconflict_help_close_start, css: '.mw-twocolconflict-help-close-start > a')
  link(:twocolconflict_help_close_end, css: '.mw-twocolconflict-help-close-end > a')
end
