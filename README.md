# Drupal 10 Custom Module: Support Page Link Redirect
This Drupal 10 custom module facilitates the management of support page links and redirections within your Drupal website. The module includes a controller to list links stored in the support page table in the database and redirects users to these links when accessing the page.

## Features
- **Support Page Link Management:** Easily manage support page links through a user-friendly interface.
- **Redirection:** Automatically redirect users to the specified support page link when they access the page.
- **Integration with Group Module:** Includes integration with the Group module, allowing members of a group to access support pages conveniently.
- **External and Internal Links:** Supports both external links (e.g., Zendesk) and internal links (e.g., Drupal node support pages) for redirection purposes.

## Installation
1. Download the module files to your Drupal modules directory (`/modules`).
2. Enable the module through the Drupal administration interface (`/admin/modules`).

## Configuration

1. After enabling the module, navigate to the module settings page (`/group/{group-id}/support`).
2. Configure the settings as per your requirements:
   - Select the method that generates the link.
   - See in (`/group/{group-id}/support-redirect`) if the link is being redirected correctly.

Here is an view example of this form:
![Support page](https://github.com/Edson-Ivo/Drupal-Simple-Form/assets/42719020/9f05eeed-39f4-4f19-8115-0724762d27d8)
![Zendesk](https://github.com/Edson-Ivo/Drupal-Simple-Form/assets/42719020/d3d8aded-5a8a-4cd7-962f-2ff8f089ed2d)
![none](https://github.com/Edson-Ivo/Drupal-Simple-Form/assets/42719020/6f88c31c-c935-4c29-be30-549c3872efac)

Here is a main page in group example:

![Main page](https://github.com/Edson-Ivo/Drupal-Simple-Form/assets/42719020/d51c74e3-8719-4325-90f0-ea615bb6f531)
