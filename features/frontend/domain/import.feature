Feature: Translation file import

    Scenario: A user can import translation file
        Given I am connected as "john"
          And I am on "/projects/tutorial/import"
         Then I should see "Import translations"

         When I attach the file "messages.en.yml" to "File"
          And I fill in the following:
            | Slug   | messages |
            | Locale | en_GB    |
          And I press "Import"
         Then I should be on "/projects/tutorial/"
          And I should see "Messages"
