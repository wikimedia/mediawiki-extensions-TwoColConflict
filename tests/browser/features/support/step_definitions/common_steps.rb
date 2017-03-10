Then(/^TwoColConflict is enabled as a beta feature$/) do
  visit(SpecialPreferencesPage).enable_twocolconflict
end

Then(/^TwoColConflict is disabled as a beta feature$/) do
  visit(SpecialPreferencesPage).disable_twocolconflict
end
