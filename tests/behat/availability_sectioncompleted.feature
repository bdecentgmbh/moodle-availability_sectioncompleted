@availability @availability_sectioncompleted
Feature: Restriction by other section completion
  In order to control student access to activities and sections
  As a teacher
  I need to require the completion of the activities in another section

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format | enablecompletion | numsections | initsections |
      | Course 1 | C1        | topics | 1                | 3           | 1            |
    And the following "users" exist:
      | username |
      | teacher1 |
      | student1 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity | name      | course | section | completion |
      | page     | PageName1 | C1     | 1       | 1          |
      | page     | PageName2 | C1     | 1       | 1          |
      | page     | PageName3 | C1     | 2       | 1          |
      | page     | PageName4 | C1     | 2       | 1          |

  @javascript
  Scenario: An activity stays hidden until the activities of a section are completed
    Given I am on the "PageName3" "page activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Section completion" "button" in the "Add restriction..." "dialogue"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I set the field "Section completion" to "Section 1"
    And I press "Save and return to course"
    When I am on the "Course 1" "course" page logged in as "student1"
    Then I should not see "PageName3" in the "region-main" "region"
    And I toggle the manual completion state of "PageName1"
    And I toggle the manual completion state of "PageName2"
    And I reload the page
    And I should see "PageName3" in the "region-main" "region"

  @javascript
  Scenario: A restricted section tells students how far they are
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I edit the section "2"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Section completion" "button" in the "Add restriction..." "dialogue"
    And I set the field "Section completion" to "Section 1"
    And I press "Save changes"
    When I am on the "Course 1" "course" page logged in as "student1"
    Then I should see "Not available unless: Section 1 completed. (0 of 2 so far)" in the "region-main" "region"
    And I should not see "PageName3" in the "region-main" "region"
    And I toggle the manual completion state of "PageName1"
    And I reload the page
    And I should see "Not available unless: Section 1 completed. (1 of 2 so far)" in the "region-main" "region"
    And I toggle the manual completion state of "PageName2"
    And I reload the page
    And I should not see "Not available unless" in the "region-main" "region"
    And I should see "PageName3" in the "region-main" "region"

  @javascript
  Scenario: A new activity can require the previous section
    Given I log in as "teacher1"
    And I add a "page" activity to course "Course 1" section "2"
    And I set the following fields to these values:
      | Name         | PageName5 |
      | Page content | Content 5 |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Section completion" "button" in the "Add restriction..." "dialogue"
    And I set the field "Section completion" to "Previous section"
    And I press "Save and return to course"
    When I am on the "Course 1" "course" page logged in as "student1"
    Then I should see "Not available unless: Section 1 completed." in the "region-main" "region"

  @javascript
  Scenario: A minimum number of activities is enough
    Given I am on the "PageName3" "page activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Section completion" "button" in the "Add restriction..." "dialogue"
    And I set the field "Section completion" to "Section 1"
    And I set the field "Required number of activities" to "at least 1 activity"
    And I press "Save and return to course"
    When I am on the "Course 1" "course" page logged in as "student1"
    Then I should see "Not available unless: At least 1 of the activities in Section 1 completed. (0 of 2 so far)" in the "region-main" "region"
    And I toggle the manual completion state of "PageName1"
    And I reload the page
    And I should not see "Not available unless" in the "region-main" "region"

  @javascript
  Scenario: Completion without a grade does not count as passed
    Given I am on the "PageName3" "page activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Section completion" "button" in the "Add restriction..." "dialogue"
    And I set the field "Section completion" to "Section 1"
    And I set the field "Required completion state" to "must be complete with pass grade"
    And I press "Save and return to course"
    When I am on the "Course 1" "course" page logged in as "student1"
    And I toggle the manual completion state of "PageName1"
    And I toggle the manual completion state of "PageName2"
    And I reload the page
    Then I should see "Not available unless: Section 1 completed and passed. (0 of 2 so far)" in the "region-main" "region"

  @javascript
  Scenario: Activities in a subsection count for its section
    Given I enable "subsection" "mod" plugin
    And the following "activities" exist:
      | activity   | name         | course | section | completion |
      | subsection | Homework     | C1     | 1       |            |
      | page       | HomeworkPage | C1     | 4       | 1          |
    And I am on the "PageName3" "page activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Section completion" "button" in the "Add restriction..." "dialogue"
    And the "Section completion" select box should contain "Section 1 › Homework"
    And I set the field "Section completion" to "Section 1"
    And I press "Save and return to course"
    When I am on the "Course 1" "course" page logged in as "student1"
    And I toggle the manual completion state of "PageName1"
    And I toggle the manual completion state of "PageName2"
    And I reload the page
    Then I should see "Not available unless: Section 1 completed. (2 of 3 so far)" in the "region-main" "region"
    And I toggle the manual completion state of "HomeworkPage"
    And I reload the page
    And I should not see "Not available unless" in the "region-main" "region"

  @javascript
  Scenario: The restriction is not offered when the course does not track completion
    Given the following "courses" exist:
      | fullname | shortname | enablecompletion |
      | Course 2 | C2        | 0                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C2     | editingteacher |
    And the following "activities" exist:
      | activity | name      | course |
      | page     | PageNoCmp | C2     |
    When I am on the "PageNoCmp" "page activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    Then "Section completion" "button" should not exist in the "Add restriction..." "dialogue"
