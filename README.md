# Drupal-Simple-Form
Here you will find an example of a form with Drupal 10 that uses the Group module to create a support form for group members.

* This code gerenates a Form in drupal 10 to create a Support page Redirect.
* The objective of this form is create a redirect link in a group(Group module), and if any group member has any questions, 
 he can click the link and go to the support page. 
* This form is accessible for Group admin, so only the group admin can create a redirect link to
 support page.
* When this form is submitted, the group admin will be creating a site support link that will be saved in the database,
 after that, the group main page will show an <a> tag with this link and when the group member click on it,
 it'll be redirected to group site support.
* The form has 3 options for creating the site support link:
 Support page: Creates a redirect link to a Drupal Content with the "Support Page" type.
 Zendesk: Creates a redirect link to a another page that is a zendesk.
 None: Don't creates any link and won't create a <a> tag in main page of the group.
* This form will reuse the data, it will not save all updates.


I put some coments in code to explain better;


Here is an view example of this form:
![Support page](https://github.com/Edson-Ivo/Drupal-Simple-Form/assets/42719020/9f05eeed-39f4-4f19-8115-0724762d27d8)
![Zendesk](https://github.com/Edson-Ivo/Drupal-Simple-Form/assets/42719020/d3d8aded-5a8a-4cd7-962f-2ff8f089ed2d)
![none](https://github.com/Edson-Ivo/Drupal-Simple-Form/assets/42719020/6f88c31c-c935-4c29-be30-549c3872efac)
