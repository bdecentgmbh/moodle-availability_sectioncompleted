@availability @availability_sectioncompleted
Feature: availability_sectioncompleted
  In order to control student access to activities for sections
  As a teacher
  I need to set sectioncompleted conditions which prevent student access

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format | enablecompletion | numsections |
      | Course 1 | C1        | topics | 1                |    3        |
    And the following "users" exist:
      | username |
      | teacher1 |
      | student1 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity | name      | intro     | introformat | course | content | contentformat | idnumber | section | completion|
      | page     | PageName1 | PageDesc1 | 1           | C1     | Page 1  | 1             | 1        |    1    |    1      |
      | page     | PageName2 | PageDesc2 | 1           | C1     | Page 2  | 1             | 2        |    1    |    1      |
      | page     | PageName3 | PageDesc3 | 1           | C1     | Page 3  | 1             | 3        |    2    |    1      |
      | page     | PageName4 | PageDesc4 | 1           | C1     | Page 4  | 1             | 4        |    2    |    1      |

  @javascript
  Scenario: Section completion test condition
    # Basic setup.
    Given I am on the "PageName3" "page activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Section completion" "button" in the "Add restriction..." "dialogue"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I set the field "Section completion" to "Topic 1"
    And I press "Save and return to course"

    # Log back in as student.
    When I am on the "Course 1" "course" page logged in as "student1"

    # Page 2 should not appear yet.
    Then I should not see "PageName3" in the "region-main" "region"

    # Mark PageName1 complete
    And I toggle the manual completion state of "PageName1"
    And the manual completion button of "PageName1" is displayed as "Done"
    And I toggle the manual completion state of "PageName2"
    And the manual completion button of "PageName2" is displayed as "Done"
    Then I reload the page
    Then I should see "PageName3" in the "region-main" "region"

  @javascript
  Scenario: Section completion for previous section to an activity
    Given I am on the PageName3 "page activity editing" page logged in as teacher1
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Section completion" "button" in the "Add restriction..." "dialogue"
    And I set the field "Section completion" to "Previous section"
    Then I press "Save and return to course"
    Then I log out
    And I log in as "student1"
    Then I am on "Course 1" course homepage
    Then I should see "Not available unless: Topic 1 completed" in the "region-main" "region"
    And I toggle the manual completion state of "PageName1"
    And the manual completion button of "PageName1" is displayed as "Done"
    And I toggle the manual completion state of "PageName2"
    And the manual completion button of "PageName2" is displayed as "Done"
    Then I reload the page
    Then I should not see "Not available unless: Topic 1 completed" in the "region-main" "region"

  @javascript
  Scenario: Section completion for previous section to an section
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    When I edit the section "2"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Section completion" "button" in the "Add restriction..." "dialogue"
    And I set the field "Section completion" to "Previous section"
    Then I press "Save changes"
    Then I log out
    And I log in as "student1"
    Then I am on "Course 1" course homepage
    Then I should see "Not available unless: Topic 1 completed" in the "region-main" "region"
    Then I should not see "PageName3" in the "region-main" "region"
    Then I should not see "PageName4" in the "region-main" "region"
    And I toggle the manual completion state of "PageName1"
    And the manual completion button of "PageName1" is displayed as "Done"
    And I toggle the manual completion state of "PageName2"
    And the manual completion button of "PageName2" is displayed as "Done"
    Then I reload the page
    Then I should not see "Not available unless: Topic 1 completed" in the "region-main" "region"
    Then I should see "PageName3" in the "region-main" "region"
    Then I should see "PageName4" in the "region-main" "region"
