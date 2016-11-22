class EditConflictPage
  include PageObject

  div(:twocolconflict_explanation_header, class: 'mw-twocolconflict-explainconflict')
  div(:twocolconflict_changes_desc, css: '.mw-twocolconflict-changes-col .mw-twocolconflict-col-desc')
  div(:twocolconflict_changes_text, id: 'mw-twocolconflict-changes-editor')
  div(:twocolconflict_editor_desc, css: '.mw-twocolconflict-editor-col .mw-twocolconflict-col-desc')
  text_area(:twocolconflict_editor_text, css: '.mw-twocolconflict-editor-col textarea')
  div(:twocolconflict_changes_same, css: '#mw-twocolconflict-changes-editor .mw-twocolconflict-diffchange-same')
  div(:twocolconflict_changes_foreign, css: '#mw-twocolconflict-changes-editor .mw-twocolconflict-diffchange-foreign')
  div(:twocolconflict_changes_own, css: '#mw-twocolconflict-changes-editor .mw-twocolconflict-diffchange-own')
  div(:twocolconflict_changes_title_foreign, css: '#mw-twocolconflict-changes-editor .mw-twocolconflict-diffchange-foreign .mw-twocolconflict-diffchange-title')
  div(:twocolconflict_changes_title_own, css: '#mw-twocolconflict-changes-editor .mw-twocolconflict-diffchange-own .mw-twocolconflict-diffchange-title')
end
