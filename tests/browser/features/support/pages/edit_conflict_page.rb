class EditConflictPage
  include PageObject

  div(:twocolconflict_explanation_header, class: 'mw-twocolconflict-explainconflict')
  div(:twocolconflict_changes_desc, css: '.mw-twocolconflict-changes-col .mw-twocolconflict-col-desc')
  div(:twocolconflict_changes_text, id: 'mw-twocolconflict-changes-editor')
  div(:twocolconflict_editor_desc, css: '.mw-twocolconflict-editor-col .mw-twocolconflict-col-desc')
  text_area(:twocolconflict_editor_text, css: '.mw-twocolconflict-editor-col textarea')
  div(:twocolconflict_changes_same, css: '#mw-twocolconflict-changes-editor .mw-twocolconflict-diffchange-same')
  span(
      :twocolconflict_changes_same_full,
      css: '#mw-twocolconflict-changes-editor .mw-twocolconflict-diffchange-same-full'
  )
  span(
      :twocolconflict_changes_same_collapsed,
      css: '#mw-twocolconflict-changes-editor .mw-twocolconflict-diffchange-same-collapsed'
  )
  button(
      :twocolconflict_collapse_changes,
      css: '.mw-twocolconflict-diffchange-same-full .mw-twocolconflict-expand-collapse-btn > button'
  )
  button(
      :twocolconflict_expand_changes,
      css: '.mw-twocolconflict-diffchange-same-collapsed .mw-twocolconflict-expand-collapse-btn > button'
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

  div(:twocolconflict_base_dialog, css: '.mw-twocolconflict-base-dialog')
  text_field(:twocolconflict_base_option,  name: 'mw-twocolconflict-base-version')
  label(:twocolconflict_base_mine_label, css: '.mw-twocolconflict-base-dialog-radio label:nth-of-type(2)')
  link(:twocolconflict_base_submit, css: '.mw-twocolconflict-base-dialog .oo-ui-buttonElement-button')

  def wait_for_help_dialog_to_hide
    wait_until do
      !twocolconflict_help_dialog_element.visible?
    end
  end
end
