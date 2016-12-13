=== Comment Mail ===

Stable tag: 161213
Requires at least: 4.4
Tested up to: 4.8-alpha
Text Domain: comment-mail

License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Contributors: WebSharks, JasWSInc, raamdev, kristineds, renzms
Donate link: http://www.websharks-inc.com/r/wp-theme-plugin-donation/
Tags: comments, subscribe, comment subscription, comment subscriptions, comment subscribe, subscribe comments, comment, comment email, comment notification, notifications, MailChimp

Email comment subscriptions for WordPress

== Description ==

Comment Mail is a powerhouse plugin that allows commenters to sign up for email notifications whenever they leave a comment on your site. Or, they can also subscribe without commenting—the choice is theirs.

= Core Plugin Features =

See also: <http://comment-mail.com/features/>

- **Comment Subscriptions**
  Notify comment authors whenever a new comment is added to a specific post (of any kind) in WordPress. Comment authors can be notified about "all" new comments, or only about replies to their own comment (most popular). This functionality is missing from WordPress, but Comment Mail fills this role beautifully with its primary focus being on comment-related email notifications. Powerful!

- **Nifty Post Meta Box in WordPress**
  When you create or edit a WordPress post, Comment Mail is right there with you! This is a feature that you never knew you wanted, but _cannot_ live without once you try it on for size. Every WordPress post becomes a new mailing list of sorts, with every comment being a potential subscriber. With each new comment, others in the thread may be updated too, which brings your site to life with a vibrant discussion.

  The Post Meta Box widget provided by Comment Mail gives you a quick look at the activity on any given WordPress Post. It includes counters that link over to a more detailed look at your list of subscribers. This is easy to configure, and you can choose to exclude this for certain types of WordPress posts if you want to. e.g., portfolios or snippets you might want to exclude as they generally do not receive comments.

- **Comment Form Subscription Options**
  Using the Comment Mail dashboard panel you can customize all of the options that are presented whenever someone leaves a comment on your WordPress posts. For example; the template file itself, JavaScript, CSS, the default option selected by commenters (e.g., do not subscribe, replies only, or subscribe to everything), and much more.

  Comment Mail integrates with just about any WordPress theme you select. Your theme only needs to support the standard WordPress hook for comment form additions. For most theme developers that's a no-brainer, and it's easy to add yourself if you happen to be an exception to the rule; i.e., in cases where your theme perhaps does not have support for the default WordPress hook yet. We provide instructions.

- **Subscribe Without Commenting**
  Ordinarily, Comment Mail subscriptions are associated with commenters; i.e., people that have something to say, and they want to be notified via email whenever a reply to their comment is posted. However, not everyone has something they want to post right away. So, perhaps they'll enjoy monitoring the conversation via email. Comment Mail makes it easy for these spectators to subscribe. They can simply choose to subscribe without leaving a comment :-)

- **Asynchronous Email Delivery**
  Do you have thousands of comments? No problem! Comment Mail is built to handle large sites with a _lot_ of users. Or, with a _lot_ of comments, perhaps even on a single WordPress post. Comment Mail enqueues every notification that is to be delivered. Comment Mail uses its own dedicated asynchronous mail queue, which is processed quietly and efficiently behind-the-scenes. This eliminates any delay in processing of the comment and enqueuing process itself, so that Comment Mail will have nothing but a positive impact on your community overall.

- **Custom Email Message Headers**
  You can adjust the `Return-Path:`, the `From:` name and email address, the `Reply-To:` email address, and even test all of your current email configuration options right inside the WordPress Dashboard using the Comment Mail interface—it's built right into WordPress with a seamless integration.

- **CAN-SPAM Compliant**
  All emails sent by Comment Mail are CAN-SPAM compliant. Comment Mail makes it easy for you to customize the postal address that is associated with your organization, and you can attach a privacy policy URL also. These are both requirements for your site to remain CAN-SPAM compliant; i.e., these components help you avoid problems with email being delivered to the spam/bulk folder in popular email clients.

- **Auto-Subscribe**
  With Comment Mail you can automatically subscribe the author of a WordPress post (of any kind) whenever it is published. This is optional, but highly recommended. You can also add a list of additional email addresses that should be subscribed automatically, choose the default email delivery option for Auto-Subscribe recipients, and even narrow this down to a specific list of Post Types in WordPress where Auto-Subscribe functionality should be applied; or where it should not be.

- **Auto-Confirm**
  By default, each subscriber is asked to confirm their subscription via email. However, Comment Mail can be configured to auto-confirm subscribers who have subscribed to other posts on your blog already. Nice!

  Or, if you like, it is also possible to force an auto-confirm for every subscriber; i.e., bypass the email confirmation message entirely. This is helpful for sites that restrict access already in some way, or in cases where a comment is associated with an already-authenticated user.

- **Front-End Subscription Management**
  Comment Mail exposes a powerful UI on the front-end of your site, which allows each comment author to review a list of their current subscriptions on your site, add new subscriptions on their own, or unsubscribe from one (or all) of their subscriptions easily.

- **Back-End Subscription Management**
  From your WordPress Dashboard you can list all subscribers by post, by email address, by comment, search the list with custom criteria, and even filter and sort the results. The Comment Mail dashboard also makes it easy to perform bulk actions. For instance, to delete many subscribers, suspend them, reconfirm them, or just to review a list of those who signed up but never actually followed through on the confirmation process.

  In addition to a list of subscriptions, Comment Mail also maintains an event log, which exposes a lot of useful information that happens along the way. If you're ever confused about why a particular user is subscribed (or not), the event log comes in handy. Not just for you, but if you ever need to explain something to a user this is handy.

- **Customizable Templates**
  The lite version of Comment Mail includes a basic set of templates that you can customize to match the theme of your site. Customize them all! Or, perhaps you simply want to tweak Comment Mail in ways that perfect the design in a particular WordPress theme of your choosing. That's fine too!

  Comment Mail templates are all right in your Dashboard, ready to edit, with instructions to help you out. You can edit templates used for the on-site display of certain components, and you can also edit the email-related templates that are associated with comment notifications sent to subscribers; e.g., the email confirmation request, or the list of comment updates that subscribers receive when a new reply is posted by a user.

- **StCR (Subscribe to Comments Reloaded) Compat.**
  Comment Mail is a highly evolved plugin that is based on the original StCR (Subscribe to Comments Reloaded) plugin for WordPress. StCR gained enormous popularity in the WordPress community over a span of several years. This new improved version of StCR (now referred to as Comment Mail™) is backward compatible with most features provided by StCR.

  If you were previously running StCR, you can upgrade to Comment Mail right away. Many of your existing StCR options are automatically interpreted by Comment Mail whenever you activate it from the WordPress Dashboard. This saves you time and makes the transition much easier.

  In addition, if you were previously running StCR, you will be prompted by Comment Mail upon activation. Comment Mail will ask you to begin an import of all of your existing StCR subscribers so that you won't lose any of those important people that establish your fan base.

- **Data Safeguards**
  By default, if you delete Comment Mail using the plugins menu in WordPress, no data is lost; i.e., Comment Mail will safeguard your configuration and all subscribers. However, if you want to completely uninstall Comment Mail you can turn Safeguards off, and _then_ deactivate & delete Comment Mail from the plugins menu in WordPress. This way Comment Mail will erase your options for the plugin, erase database tables created by the plugin, remove subscriptions, terminate CRON jobs, etc. In short, when Safeguards are off, Comment Mail erases itself from existence completely when you delete it.

= Enhanced / Pro-Only Features =

See also: <http://comment-mail.com/features/>

_**TIP:** you can preview Pro features in the free version by clicking the "Preview Pro Features" link at the top of your Comment Options options panel._

- **SMTP Server Integration**
  Comment Mail Pro makes it possible for you to configure a dedicated SMTP server that is specifically for comment-related emails. For instance, many pro users use this feature to integrate Comment Mail with services like Mandrill or Amazon SES. This can dramatically improve your email delivery rate when it comes to email notifications that subscribers receive.

  Of course, it also has the side benefit of reducing load on your own server by moving email delivery to a more capable service that is specifically designed for such a thing.

- **RVE (Replies Via Email)**
  Comment Mail Pro makes it very easy for subscribers to reply to a notification they receive via email—_with_ an email! In short, instead of a subscriber (i.e., someone that receives an update via email) being forced to return to your site, they can simply reply via email. Their response magically ends up on your site in the proper comment thread—just as if they _had_ taken the time to return to your site.

  The best part about this feature is that it requires no special server requirements, and very little configuration. This is because Comment Mail Pro has been integrated with both Mandrill and SparkPost for RVE service. So all you need to do is follow the simple instructions to setup a Mandrill or SparkPost account. Then supply Comment Mail with the email address that you configure there.

- **SSO (Single Sign-On) Integration**
  This powerful functionality in Comment Mail Pro attracts _many_ more comments (thus, subscribers). With SSO enabled a commenter can be identified by simply logging into your site with Facebook, Twitter, Google+, or LinkedIn.

  These popular social networks were integrated with Comment Mail Pro, because many visitors actually _prefer_ to be identified using their social profile instead of being forced to register again and again on every site they want to leave a comment on.

  This feature also has the side benefit of turning commenters into registered users; i.e., users are first identified (quickly) by their Facebook, Twitter, Google+, or LinkedIn account, but then Comment Mail _also_ quietly creates an account for them on your site too. This way you acquire a new user in the process; i.e., you collect a new subscriber and also a new registered user.

- **Impressive Statistics/Charts**
  Comment Mail Pro adds a new menu option which links to a set of advanced reporting tools that can be used to generate statistical charts/graphs. Yay! ~ You can review and compare total subscribers, most popular posts, least popular posts, confirmation percentages, suspension percentages, subscriptions by post ID, by country, by region, and more. This information is critical when you need to  better understand your subscribers, and it's a unique component of your site that only Comment Mail can accurately report on.

- **Email Blacklisting**
  Comment Mail Pro makes it possible for a site owner to configure a list of patterns that will be used to blacklist certain email addresses. For instance, it is a good idea to blacklist role-based email addresses like `admin@*`, `webmaster@*`, `postmaster@*`, etc. With Comment Mail Pro you gain complete control over the blacklist that is applied internally.

- **Geo IP Region/Country Tracking**
  Comment Mail Pro can post user IP addresses to a remote geoPlugin API behind-the-scenes, asking for geographic data associated with each subscription. Comment Mail will store this information locally in your WP database so that the data can be exported easily, and even used in statistical reporting by Comment Mail itself.

- **Advanced PHP-Based Templates**
  Comment Mail Pro comes with two different template options. The default template set is a very basic shortcode-like syntax that is easy for novice site owners to work with. However, developers might prefer to switch to the advanced template mode, where they gain complete and total control over all of Comment Mail's PHP-based templates.

  In either case (simple or advanced) all of Comment Mail's template files are easily modified using the Comment Mail dashboard; i.e., from within WordPress, where instructions are provided, along with dynamic replacement codes and documented variables that are used in each template file.

- **Misc. UI-Related Settings**
  In Comment Mail Pro, you can tune-in 7-10 different UI-related options that impact users on both the front and back end of Comment Mail in different ways. For instance, you can alter the number of subscriptions that appear on each page in a summary that subscribers see when they view a list of their subscriptions. You can also adjust the way WordPress post/comment dropdown menus are presented when people choose to subscribe without commenting.

- **Import and/or Mass Update**
  With Comment Mail Pro you can import and/or mass update subscribers from the WordPress dashboard. Comment Mail's importer will accept direct CSV input in a textarea, or you can choose to upload a prepared CSV file. This allows you to bring existing subscribers (from any platform) into a WordPress blog and keep users up-to-date with comments that occur on your site.

- **Subscriber Export**
  Comment Mail Pro makes it easy to export some (or all) of your subscribers into one (or more) downloadable CSV files. This feature ensures that your subscribers remain portable; should you decide to change platforms or move subscribers to another site in the future.

- **Import/Export Config. Options**
  This feature is a _huge_ time-saver if you run more than one site where Comment Mail is being used. You can import your options from another WordPress installation where you've already configured Comment Mail before; i.e., you can easily duplicate an established configuration and save time setting up the new site.

- **Subscription Cleaner Tuning**
  Comment Mail automatically deletes unconfirmed and trashed subscriptions after X number of hours/minutes/days. It runs automatically via WP-Cron, and the subscription cleaner can be configured in a number of ways. Comment Mail Pro makes it possible for you to tune-in this feature (i.e., customize your configuration)—or even disable it entirely if prefer.

- **Log Cleaner Adjustments**
  Comment Mail automatically deletes very old event log entries after X number of hours/minutes/days. It runs automatically via WP-Cron, and the log cleaner can be configured in a number of ways. Comment Mail Pro makes it possible for you to tune-in this feature (i.e., customize your configuration)—or even disable it entirely if prefer.

- **Queue Processor Adjustments**
  Performance tuning Comment Mail can be important on very large sites. With Comment Mail Pro you'll have full control over the asynchronous email notification processing queue. For instance, you can control how often Comment Mail's queue processor runs, how long it will run each time, the max number of emails it will process in each run, and more.

- **MailChimp Integration**
  Integrate with MailChimp to give users the option of subscribing to your site-wide mailing list whenever they leave a comment or post a new reply. In addition to subscribing to comment reply notifications they can also choose to join your MailChimp mailing list.

== Screenshots ==

1. Screenshot #1
2. Screenshot #2
3. Screenshot #3
4. Screenshot #4
5. Screenshot #5

== Installation ==

= Comment Mail™ is very easy to install... =

See also: <http://comment-mail.com/installation/>

1. Upload the `/comment-mail` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the Plugins menu in WordPress®.
3. Navigate to the **Comment Mail** panel & enable.

== Frequently Asked Questions ==

- Please see: <http://comment-mail.com/kb/kb-tag/pre-sale-faqs/>

== Enhanced / Pro-Only Features ==

See also: <http://comment-mail.com/features/>

_**TIP:** you can preview Pro features in the free version by clicking the "Preview Pro Features" link at the top of your Comment Options options panel._

- **SMTP Server Integration**
  Comment Mail Pro makes it possible for you to configure a dedicated SMTP server that is specifically for comment-related emails. For instance, many pro users use this feature to integrate Comment Mail with services like Mandrill or Amazon SES. This can dramatically improve your email delivery rate when it comes to email notifications that subscribers receive.

  Of course, it also has the side benefit of reducing load on your own server by moving email delivery to a more capable service that is specifically designed for such a thing.

- **RVE (Replies Via Email)**
  Comment Mail Pro makes it very easy for subscribers to reply to a notification they receive via email—_with_ an email! In short, instead of a subscriber (i.e., someone that receives an update via email) being forced to return to your site, they can simply reply via email. Their response magically ends up on your site in the proper comment thread—just as if they _had_ taken the time to return to your site.

  The best part about this feature is that it requires no special server requirements, and very little configuration. This is because Comment Mail Pro has been integrated with both Mandrill and SparkPost for RVE service. So all you need to do is follow the simple instructions to setup a Mandrill or SparkPost account. Then supply Comment Mail with the email address that you configure there.

- **SSO (Single Sign-On) Integration**
  This powerful functionality in Comment Mail Pro attracts _many_ more comments (thus, subscribers). With SSO enabled a commenter can be identified by simply logging into your site with Facebook, Twitter, Google+, or LinkedIn.

  These popular social networks were integrated with Comment Mail Pro, because many visitors actually _prefer_ to be identified using their social profile instead of being forced to register again and again on every site they want to leave a comment on.

  This feature also has the side benefit of turning commenters into registered users; i.e., users are first identified (quickly) by their Facebook, Twitter, Google+, or LinkedIn account, but then Comment Mail _also_ quietly creates an account for them on your site too. This way you acquire a new user in the process; i.e., you collect a new subscriber and also a new registered user.

- **Impressive Statistics/Charts**
  Comment Mail Pro adds a new menu option which links to a set of advanced reporting tools that can be used to generate statistical charts/graphs. Yay! ~ You can review and compare total subscribers, most popular posts, least popular posts, confirmation percentages, suspension percentages, subscriptions by post ID, by country, by region, and more. This information is critical when you need to  better understand your subscribers, and it's a unique component of your site that only Comment Mail can accurately report on.

- **Email Blacklisting**
  Comment Mail Pro makes it possible for a site owner to configure a list of patterns that will be used to blacklist certain email addresses. For instance, it is a good idea to blacklist role-based email addresses like `admin@*`, `webmaster@*`, `postmaster@*`, etc. With Comment Mail Pro you gain complete control over the blacklist that is applied internally.

- **Geo IP Region/Country Tracking**
  Comment Mail Pro can post user IP addresses to a remote geoPlugin API behind-the-scenes, asking for geographic data associated with each subscription. Comment Mail will store this information locally in your WP database so that the data can be exported easily, and even used in statistical reporting by Comment Mail itself.

- **Advanced PHP-Based Templates**
  Comment Mail Pro comes with two different template options. The default template set is a very basic shortcode-like syntax that is easy for novice site owners to work with. However, developers might prefer to switch to the advanced template mode, where they gain complete and total control over all of Comment Mail's PHP-based templates.

  In either case (simple or advanced) all of Comment Mail's template files are easily modified using the Comment Mail dashboard; i.e., from within WordPress, where instructions are provided, along with dynamic replacement codes and documented variables that are used in each template file.

- **Misc. UI-Related Settings**
  In Comment Mail Pro, you can tune-in 7-10 different UI-related options that impact users on both the front and back end of Comment Mail in different ways. For instance, you can alter the number of subscriptions that appear on each page in a summary that subscribers see when they view a list of their subscriptions. You can also adjust the way WordPress post/comment dropdown menus are presented when people choose to subscribe without commenting.

- **Import and/or Mass Update**
  With Comment Mail Pro you can import and/or mass update subscribers from the WordPress dashboard. Comment Mail's importer will accept direct CSV input in a textarea, or you can choose to upload a prepared CSV file. This allows you to bring existing subscribers (from any platform) into a WordPress blog and keep users up-to-date with comments that occur on your site.

- **Subscriber Export**
  Comment Mail Pro makes it easy to export some (or all) of your subscribers into one (or more) downloadable CSV files. This feature ensures that your subscribers remain portable; should you decide to change platforms or move subscribers to another site in the future.

- **Import/Export Config. Options**
  This feature is a _huge_ time-saver if you run more than one site where Comment Mail is being used. You can import your options from another WordPress installation where you've already configured Comment Mail before; i.e., you can easily duplicate an established configuration and save time setting up the new site.

- **Subscription Cleaner Tuning**
  Comment Mail automatically deletes unconfirmed and trashed subscriptions after X number of hours/minutes/days. It runs automatically via WP-Cron, and the subscription cleaner can be configured in a number of ways. Comment Mail Pro makes it possible for you to tune-in this feature (i.e., customize your configuration)—or even disable it entirely if prefer.

- **Log Cleaner Adjustments**
  Comment Mail automatically deletes very old event log entries after X number of hours/minutes/days. It runs automatically via WP-Cron, and the log cleaner can be configured in a number of ways. Comment Mail Pro makes it possible for you to tune-in this feature (i.e., customize your configuration)—or even disable it entirely if prefer.

- **Queue Processor Adjustments**
  Performance tuning Comment Mail can be important on very large sites. With Comment Mail Pro you'll have full control over the asynchronous email notification processing queue. For instance, you can control how often Comment Mail's queue processor runs, how long it will run each time, the max number of emails it will process in each run, and more.

- **MailChimp Integration**
  Integrate with MailChimp to give users the option of subscribing to your site-wide mailing list whenever they leave a comment or post a new reply. In addition to subscribing to comment reply notifications they can also choose to join your MailChimp mailing list.

== Software Requirements ==

In addition to the [WordPress Requirements](http://wordpress.org/about/requirements/), Comment Mail™ requires the following minimum versions:

- PHP 5.4+
- Apache 2.1+

== License ==

Copyright: © 2013 [WebSharks, Inc.](http://www.websharks-inc.com/bizdev/) (coded in the USA)

Released under the terms of the [GNU General Public License](http://www.gnu.org/licenses/gpl-2.0.html).

= Credits / Additional Acknowledgments =

* Software designed for WordPress®.
	- GPL License <http://codex.wordpress.org/GPL>
	- WordPress® <http://wordpress.org>
* Some JavaScript extensions require jQuery.
	- GPL-Compatible License <http://jquery.org/license>
	- jQuery <http://jquery.com/>
* CSS framework and some JavaScript functionality provided by Bootstrap.
	- GPL-Compatible License <http://getbootstrap.com/getting-started/#license-faqs>
	- Bootstrap <http://getbootstrap.com/>
* Icons provided by Font Awesome.
	- GPL-Compatible License <http://fortawesome.github.io/Font-Awesome/license/>
	- Font Awesome <http://fortawesome.github.io/Font-Awesome/>

== Upgrade Notice ==

= v150709 =

Requires PHP v5.4+.

== Changelog ==

= v161213 =

- **Bug Fix:** Prevent browser autocomplete in Comment Mail options. See [Issue #319](https://github.com/websharks/comment-mail/issues/319).
- **Bug Fix:** Searching by email address alone should always narrow to the search to that specific email address and not result in any fuzzy or fulltext matching. See [Issue #226](https://github.com/websharks/comment-mail/issues/226).
- **Bug Fix:** The conflict check for 'Subscribe to Comments Reloaded' was not working in the previous release; i.e., if you attempt to activate both Comment Mail and the 'Subscribe to Comments Reloaded' plugin at the same, this should result in a Dashboard warning. Fixed in this release. See [Issue #315](https://github.com/websharks/comment-mail/issues/315).
- **Bug Fix:** Notify 'Subscribe to Comments Reloaded' users about the comment form template being disabled under certain scenarios. See [Issue #314](https://github.com/websharks/comment-mail/issues/314).
- **Bug Fix:** Do not attempt to import 'Subscribe to Comments Reloaded' (StCR) settings if StCR is no longer installed, even if old StCR options exist in the database. See [Issue #294](https://github.com/websharks/comment-mail/issues/294).
- **Bug Fix** (Pro): Do not show SparkPost partner image when Mandrill is selected as the RVE handler. See [Issue #318](https://github.com/websharks/comment-mail/issues/318).
- **Bug Fix** (Pro): Conflict checks between lite and pro corrected. This was not working properly in the previous release; i.e., installing Comment Mail Pro when Comment Mail Lite is already running should result in Comment Mail Lite being deactivated automatically. See [Issue #270](https://github.com/websharks/comment-mail/issues/270).
- **New Feature:** It is now possible to manually process the outgoing mail queue. See: **WP Dashboard → Comment Mail → Mail Queue**. See also [Issue #282](https://github.com/websharks/comment-mail/issues/282).
- **New Feature** (Pro): In Comment Mail Pro it is now possible to enable/disable comment content clipping entirely; e.g., if you prefer that email notifications include the full original comment content in raw HTML instead of being clipped and displayed in the email as plain text. See: **WP Dashboard → Comment Mail → Config. Options → Email Notification Clips**. See also: [Issue #281](https://github.com/websharks/comment-mail/issues/281).
- **Accessibility:** This release improves screen reader accessibility by adding `aria-hidden="true"` to all FontAwesome icons. See [Issue #304](https://github.com/websharks/comment-mail/issues/304).
- **Accessibility:** This release improves screen reader accessibility by offering a new setting that allows a site owner to enable or disable select menu option enhancement via jQuery. Disabling select menu option enhancement has the benefit of improving accessibility for screen readers whenever accessibility is of more concern than presentation. See: **Dashboard → Comment Mail → Config Options → Misc. UI-Related Settings**. See also [Issue #304](https://github.com/websharks/comment-mail/issues/304).

= v161129 =

- **Bug Fix:** This release corrects a nasty bug that was first introduced in the previous release, resulting in the loss of template modifications when/if any Comment Mail options were edited after having upgraded to the previous release. See [Issue #322](https://github.com/websharks/comment-mail/issues/322). In short, please avoid v161118 (the previous release).

  _**Note:** If you already upgraded to v161118, but you have not edited your Comment Mail options yet (or had no template modifications anyway), there is no cause for alarm._

  _However, if you upgraded to v161118 and **did** edit your Comment Mail options after updating, and if you also modified Comment Mail templates, you may have experienced a loss of template data; i.e., the changes you made to the default templates may have been lost as a result of this bug. We are very sorry about this. Please see [Issue #322](https://github.com/websharks/comment-mail/issues/322) for additional details._

= v161118 =

- **Bug Fix:** Exclude unapproved comments from the dropdown menu on the 'Subscribe Without Commenting' form. See [Issue #299](https://github.com/websharks/comment-mail/issues/299).
- **Enhancement:** When parsing templates, this release uses `include()` instead of `eval()` whenever possible. This improves compatibility with shared hosting providers and makes it easier to debug template parsing issues should they occur. See [Issue #192](https://github.com/websharks/comment-mail/issues/192).
- **Enhancement:** When parsing templates, this release uses `include()` instead of `eval()` whenever possible. This improves compatibility with shared hosting providers and makes it easier to debug template parsing issues should they occur. See [Issue #192](https://github.com/websharks/comment-mail/issues/192).
- **PHP v7 Compatibility:** After further testing, it was found that while Comment Mail is compatible with PHP v7.0, you must be running PHP v7.0.9+. Starting with this release of Comment Mail if you're running PHP v7 < 7.0.9 a warning is displayed in the WordPress Dashboard asking the site owner to upgrade to PHP v7.0.9 or higher. Note: While PHP v7.0.9 is adequate, PHP v7.0.10 is strongly recommended. See [Issue #272](https://github.com/websharks/comment-mail/issues/272).
- **New SparkPost Integration** (Pro):  It is now possible to use SparkPost for Replies-via-Email (RVE) instead of Mandrill. Note that SparkPost is now the suggested RVE Handler for Comment Mail because Mandrill changed its pricing structure a while back. In short, Mandrill requires a fee, whereas SparkPost (recommended) offers Relay Webhooks as a part of their free plan. See [Issue #265](https://github.com/websharks/comment-mail/issues/265).
- **New Feature** (Pro): Adding support for MailChimp integration. It is now possible to give users the option of subscribing to your site-wide mailing list whenever they leave a comment or reply; i.e., in addition to subscribing to comment reply notifications they can also join your MailChimp mailing list. See [Issue #114](https://github.com/websharks/comment-mail/issues/114).

= v160824 =

- **Bug Fix**: Fixed a bug that was generating a fatal error when replying to comments. This bug only affected the Lite version of Comment Mail and was introduced by the previous release (v160818) where [work](https://github.com/websharks/comment-mail/issues/285) was done to strip out unused Pro-only code from the Lite codebase. A few references to Pro-only functions were missed and that resulted in fatal errors for the Lite version in some scenarios. See [Issue #297](https://github.com/websharks/comment-mail/issues/297).

= v160818 =

- **Bug Fix**: Fixed a bug where the "My Comment Subscriptions" link would appear on the Add New Subscription page (when Subscribing without Commenting) and would lead to a page that displayed an error message stating that there were no subscriptions to list. That link is now hidden when there are no subscriptions to list. Props @Reedyseth @kristineds. See [Issue #229](https://github.com/websharks/comment-mail/issues/229).
- **Bug Fix** (Pro): Removed an erroneous anchor tag in the Advanced Template for Comment Notification Message Body. Props @kristineds. See [Issue #287](https://github.com/websharks/comment-mail/issues/287).
- **UI Enhancement:** Improved the nav bar at the top of the options pages to reduce unnecessary whitespace. Also moved the Restore button to the nav bar so that it's not so prominent. Props @renzms. See [Issue #284](https://github.com/websharks/comment-mail/issues/284).
- **UI Enhancement:** Added links to the Comment Mail [Twitter](http://twitter.com/CommentMail) and [Facebook](https://www.facebook.com/Comment-Mail-565683256946855/) pages to the nav bar on the options page. Props @renzms. See [Issue #286](https://github.com/websharks/comment-mail/issues/286).
- **UX Enhancement:** Removed IP address information from email notification templates to better comply with data protection laws in certain countries. Props @kristineds. See [Issue #288](https://github.com/websharks/comment-mail/issues/288).
- **SEO Improvement:** Added `rel="nofollow"` to the "Subscribe without Commenting" link and "Manage Subscriptions" link on the comment subscription form to avoid indexing or transferring PageRank. Props @IvanRF. See [Issue #80](https://github.com/websharks/comment-mail-pro/pull/80).
- Removed several development-only files from the distributable that were inadvertently included during the build process. See [Issue #285](https://github.com/websharks/comment-mail/issues/285).
- Added Renz Sevilla (`renzms`) to the contributors list.

= v160618 =

- **Restructured Codebase**: The codebase has been completely restructured to improve performance, enhance flexibility, and make it easier to build in new features! Props @jaswsinc. See [Issue #150](https://github.com/websharks/comment-mail/issues/150).
- **Comment Mail Pro Upgrade Notice: Incompatible Advanced Templates.** This version of Comment Mail includes a rewritten and improved codebase. This rewrite, however, came with the unfortunate side effect of breaking backwards compatibility with Advanced Templates that were customized in a previous version of Comment Mail Pro.

     If you are currently using Comment Mail Pro and you've customized your Advanced Templates, all of your customized Advanced Templates will be backed up and the templates will then be reset to their new defaults. You will find the backup of your old customized template appended to the bottom of the new template, separated with a  <code>Legacy Template Backup</code> PHP comment. See [example screenshots](https://github.com/websharks/comment-mail/issues/238#issuecomment-225029042).

     Note: This change has no effect on Simple templates—only Advanced Templates are affected. Advanced Templates are a Pro-only feature, so this notice only applies to Comment Mail Pro. See [Issue #238](https://github.com/websharks/comment-mail/issues/238).
- **Bug Fix**: Fixed a bug where `esc_html()` was being used where `esc_sql()` should've been used. Props @jaswsinc @kristineds. See [Issue #268](https://github.com/websharks/comment-mail/issues/268).
- **Bug Fix**: Fixed a bug that in some scenarios resulted in a "DB table creation failure" error when activating the plugin. Props @thienhaxanh2405, @PanNovak, @kristineds, and @jaswsinc. See [Issue #260](https://github.com/websharks/comment-mail/issues/260).
- **Bug Fix**: Fixed a bug where "New reply" notification emails were not being parsed properly by some Hotmail accounts and were showing up as blank. Props @kristineds. See [Issue #259](https://github.com/websharks/comment-mail/issues/259).
- **Bug Fix**: Fixed a bug that allowed spam comments to create subscriptions in Comment Mail when using Akismet. Props @IvanRF. See [Issue #250](https://github.com/websharks/comment-mail/issues/250).
- **Bug Fix** (Pro): When Chrome or Firefox Autofill Username/Password was enabled, the Comment Mail Pro Updater fields would incorrectly be autofilled by the browser with invalid credentials. This has been fixed. Props @renzms. [Issue #274](https://github.com/websharks/comment-mail/issues/274).
- **Bug Fix**: Fixed a bug where the cron job for the Queue Processor could get deleted and never recreated, which would result in notifications getting stuck in the Mail Queue and not being sent out. If you ever installed Comment Mail and then deleted it (without first disabling Data Safeguards), and then installed Comment Mail again, you were probably affected by this issue. This release fixes the issue and makes the cron setup more robust. Props @kristineds, @renzms, @jaswsinc, and @IvanRF for help testing. See [Issue #194](https://github.com/websharks/comment-mail/issues/194) and [Issue #173](https://github.com/websharks/comment-mail/issues/173).
- **Bug Fix:** Fixed a bug where a subscriber who selected Hourly Digest and who had never been notified before could, in some scenarios, have their subscription treated instead as a Weekly Digest. This bug was found and fixed during the codebase restructuring. Props @jaswsinc. See [Issue #150](https://github.com/websharks/comment-mail/issues/150) and additional discussion in [Issue #173](https://github.com/websharks/comment-mail/issues/173#issuecomment-225215333).
- **Bug Fix:** Fixed a bug where in some scenarios Mail Queue entries for Digest Notifications that should have been held for sending later were not being held and were also not being sent. They also would not have shown up in the Mail Queue Event Log. This bug was found and fixed during the codebase restructuring. Props @jaswsinc. See [Issue #150](https://github.com/websharks/comment-mail/issues/150) and additional discussion in [Issue #173](https://github.com/websharks/comment-mail/issues/173#issuecomment-225215333).
- **Enhancement**: Minor improvements to the Options Page menu links and positioning of the Pro Preview link. Props @renzms. See [Issue #227](https://github.com/websharks/comment-mail/issues/227).
- **Enhancement**: It's now possible to use the following shortcodes in the Email Footer Tag for Email Footer Templates: `[home_url]`, `[blog_name_clip]`, and `[current_host_path]`. Props @kristineds and @IvanRF. See [Issue #246](https://github.com/websharks/comment-mail/issues/246).
- **Enhancement**: Improved the Subscriptions meta box that appears on the Post Edit screen. For each subscription, the meta box now lists the full name and email address, the date the subscription was created, and a view link that allows you to view/edit the subscription. Props @kristineds. See [Issue #231](https://github.com/websharks/comment-mail/issues/231).
- **UX Enhancement (Pro)**: Improved the Dashboard notice that appears when you try to enable the Pro version of Comment Mail when the Lite version is currently enabled. Props @kristineds @jaswsinc. See [Issue #230](https://github.com/websharks/comment-mail/issues/230).
- **UX Enhancement**: When Subscribing Without Commenting, the Add New Subscription form now pre-populates the Name and Email address fields whenever possible. Props @kristineds. See [Issue #204](https://github.com/websharks/comment-mail/issues/204).
- **UI Enhancement**: Dashboard notices generated by Comment Mail now use the WordPress-style dismiss button to keep things consistent. Props @kristineds. See [Issue #193](https://github.com/websharks/comment-mail/issues/193).

= v160213 =

- **Minor Fix**: Fixed a spelling mistake in one of the default email templates. Props @kristineds  @RealDavidoff. See [Issue #208](https://github.com/websharks/comment-mail/issues/208).
- **Enhancement**: Moved the default location for the Subscriptions Meta Box on the Post Edit screen so that it shows up underneath the post editing area instead of above the Publish box. See [Issue #57](https://github.com/websharks/comment-mail/issues/57#issuecomment-174482908).
- **Enhancement**: Removed an irrelevant and confusing note from the Add New Subscription page. Props @kristineds @RealDavidoff. See [Issue #207](https://github.com/websharks/comment-mail/issues/207).
- **Enhancement**: Improved the front-end Edit Subscription form and removed the "x" in on the Status and Deliver fields that allowed clearing those fields, which did not make sense since both of those fields are required. Props @kristineds. See [Issue #195](https://github.com/websharks/comment-mail/issues/195).
- **Enhancement**: Improved the way some links work by opening on-site links in current tab, and external links in new tab. Props @RealDavidoff @kristineds @renzms. See [Issue #202](https://github.com/websharks/comment-mail/issues/202).
- **Enhancement**: Improved front-end pages by using `<em>` (emphasis) tags instead of quotation marks in various areas. Props @RealDavidoff @renzms. See [Issue #201](https://github.com/websharks/comment-mail/issues/201).
- **Enhancement**: Improved email templates by simplifying the subject lines by using `[` and `]` brackets around the meta information in the subject. See [Issue #232](https://github.com/websharks/comment-mail/issues/232).
- **Enhancement**: When Subscribing Without Commenting, the Add New Subscription form now pre-populates the Name and Email address fields whenever possible. Props @kristineds. See [Issue #204](https://github.com/websharks/comment-mail/issues/204).
- **Enhancement**: Improved the consistency of how we refer to the "instant" delivery option by replacing any occurrences of "asap" with "instantly". These names were previously being mixed. We now use "instantly". Props @RealDavidoff @renzms @kristineds. See [Issue #206](https://github.com/websharks/comment-mail/issues/206).
- **Enhancement**: Added the installed version number to the plugin options pages. Props @kristineds. See [Issue #187](https://github.com/websharks/comment-mail/issues/187).
- **Enhancement**: Improved the organization of navigation links on the plugin options pages. Props @kristineds. See [Issue #187](https://github.com/websharks/comment-mail/issues/187).

= v151224 =

- **Bug Fix** (Multisite): Fixed a Multisite uninstallation bug that was preventing Comment Mail from taking Child Blogs into consideration when uninstalling, which was resulting in an incomplete uninstallation. See [Issue #136](https://github.com/websharks/comment-mail/issues/136).
- **Bug Fix** (StCR Import): This release corrects an StCR importation bug. The bug was causing an existing StCR subscription with a `Y` status (i.e., one for all comments/replies) to be imported into Comment Mail on a per-comment basis. Therefore, a symptom of this bug was to find that you had multiple subscriptions imported for users who wanted _all_ comments, instead of them just having one subscription imported which would automatically cover the entire post. See also [Issue #162](https://github.com/websharks/comment-mail/issues/162).
- **Bug Fix** (StCR Import): Fixed a bug with the StCR Import routine that was preventing subscriptions for comments awaiting approval from being imported. See [Issue #182](https://github.com/websharks/comment-mail/issues/182).
- **Bug Fix** (StCR Import): Fixed a bug in how the Subscribe to Comments Reloaded Import routine counts imported subscriptions. Comment Mail was previously reporting the number of subscriptions that it created during the import as the total number imported, however due to a difference in how Comment Mail and StCR store subscriptions, the total number of StCR subscriptions imported is almost always going to be different from the number of subscriptions that Comment Mail creates. This difference resulted in confusion about why the total StCR subscriptions did not match the total reported by Comment Mail as "imported". This has been fixed by adjusting what is reported as 'total imported' and including two new pieces of information: 'total skipped' and 'total created'. When you add the 'total imported' and 'total skipped' numbers together, that number should equal the number of subscriptions reported in StCR. Props @Reedyseth @jaswsinc. See [Issue #166](https://github.com/websharks/comment-mail/issues/166).
- **Bug Fix**: Removed a UTF-8 "Branch Icon" in the default email templates that was not showing up properly in some email clients. Props @renzms @kristineds. See [Issue #116](https://github.com/websharks/comment-mail/issues/116).
- **Bug Fix**: Fixed an email template bug that was forcing an ellipsis to show even when the length of the comment content was shorter than the max length. See [this commit](https://github.com/websharks/comment-mail/commit/80aab562fb1fe3cdcdb4f10eda7e74c8ea21aa61) for details.
- **Bug Fix**: Fixed an email template bug that was not linking the "add reply" link directly to the comment form for replying to the comment (it was just linking to the comment itself). See [this commit](https://github.com/websharks/comment-mail/commit/686b2737e708dc4a70ede129b7f4a9151c150907#commitcomment-14925636) for details.
- **Bug Fix**: Fixed a bug with "Restore Default Options" that was introducing a browser quirk that was preventing the default options from being restored. Props @renzms. See [Issue #181](https://github.com/websharks/comment-mail/issues/181).
- **Bug Fix**: Fixed a bug where certain icons would not appear, or the wrong icon would appear, when Comment Mail was installed alongside the ZenCache plugin. Both plugins use the same Sharkicons library, but were using different versions, which introduced a conflict. Comment Mail has been updated to use the latest version of the Sharkicons library. Props @Reedyseth. See [Issue #87](https://github.com/websharks/comment-mail/issues/87#issuecomment-166458711).
- **Bug Fix**: Fixed a UI bug where some of the Dashboard notices generated by Comment Mail were either misaligned or were clashing with the Subscribe to Comments Reloaded options menu. Props @renzms. See [Issue #186](https://github.com/websharks/comment-mail/issues/186).
- **Enhancement (Pro)**: Added a "Pro" label to the plugin name to clearly indicate when the Pro version of Comment Mail is installed. Props @kristineds. See [Issue #131](https://github.com/websharks/comment-mail/issues/131).
- **Enhancement (Pro)**: Added a note to the default email templates when RVE (Replies via Email) is enabled, warning email readers that "Your reply will be posted publicly and immediately." Props @Reedyseth. See [Issue #123](https://github.com/websharks/comment-mail/issues/123).
- **Enhancement (Pro):** If you have Comment Mail's SSO integration enabled and have users who are registering an account via Facebook, Twitter, LinkedIn, or Google+, the SSO service that a particular user (or commenter) signed up with is now shown in the list of Users. Props @kristineds See [Issue #73](https://github.com/websharks/comment-mail/issues/73).
- **Enhancement (Pro):** Theme Syntax Highlighting: This release makes it possible for the color-scheme used in template file syntax highlighting to be changed to any one of 30+ options. See: _Dashboard → Comment Mail → Config. Options → Template-Related Settings_. Props @kristineds. See [Issue #147](https://github.com/websharks/comment-mail/issues/147).
- **Enhancement** (StCR Import): Moved the Import Status box (for importing Subscribe to Comments Reloaded subscriptions) up underneath the "Begin StCR Auto-Importation" button to improve visibility when importing subscriptions. See [Issue #172](https://github.com/websharks/comment-mail/issues/172).
- **Enhancement** (StCR Import): The "Upgrading from Subscribe to Comments Reloaded" notice, which appears when installing Comment Mail on a site running the StCR plugin, is now automatically hidden once you run the StCR import process. See [Issue #169](https://github.com/websharks/comment-mail/issues/169).
- **Enhancement** (StCR Import): During the import process, Comment Mail now keeps track of and reports total number of StCR subscriptions skipped and total number of Comment Mail subscriptions created. Skipped subscriptions are the number of StCR subscriptions that were not imported for one of several possible reasons. See [Issue #166](https://github.com/websharks/comment-mail/issues/166).
- **Enhancement** (StCR Import): The StCR importer now generates a log file in `comment-mail-pro/stcr-import-failures.log` that includes reports of any failures or skipped subscriptions, along with information about why they failed or why they were skipped. Note that this file is only created if there were failures or skipped subscriptions during import. See [Issue #166](https://github.com/websharks/comment-mail/issues/166).
- **Enhancement**: Removed references to "CAN-SPAM Act", which is a United States law that may not be understood or applicable to Comment Mail users outside of the United States. Props @Li-An and @kristineds. See [Issue #122](https://github.com/websharks/comment-mail/issues/122).
- **Enhancement**: Added the default translation file. Translators can now build translations using the default translation file located in `includes/translations/`. Props @kristineds. See [Issue #118](https://github.com/websharks/comment-mail/issues/118).
- **Enhancement**: Improved the on-hover color when hovering the mouse over Option Panels in the Comment Mail Options by changing it from green to dark blue. Props @kristineds. See [Issue #117](https://github.com/websharks/comment-mail/issues/117).
- **Enhancement**: Improved the Subscriptions table and the Event Log UI by using proper case for Status and Event values. Props @kristineds. See [Issue #72](https://github.com/websharks/comment-mail/issues/72).
- **Enhancement**: Comment Mail is now only enabled for the standard `post` Post Type by default and there's a new "Enable for Post Types" option inside _Comment Mail → Config. Options → Enable/Disable_ that allows you to specify a comma-delimited list of Post Types that Comment Mail should be enabled for. It's also possible to enable Comment Mail on all Post Types that support comments by leaving the field empty (this was the previous behavior). Props to @bonest. See [Issue #149](https://github.com/websharks/comment-mail/issues/149).
- **Enhancement**: Improved Dashboard notices. The "Upgrading from Subscribe to Comments Reloaded" message has been improved and a new Dashboard notice lets you know when Comment Mail is disabled. Props @kristineds. See [Issue #163](https://github.com/websharks/comment-mail/issues/163).
- **Enhancement**: Many improvements the default email templates. The templates are now fully responsive and look much better on mobile devices. The wording in several places was also cleaned up for clarity and consistency and any references to an ID# (which is generally useless for readers) have been removed. The default templates also use a single font style now instead of mixing font styles and the email templates no longer use tables (this improves responsiveness). If you've modified the default Comment Mail email templates on your site, your changes will not be overwritten by updating, however if you've modified the templates and you want to get these changes, you'll need to reset to the default email templates and make your changes again (you can do this by emptying the email template fields completely and saving your options). See [Issue #77](https://github.com/websharks/comment-mail/issues/77).
- **Enhancement**: Ellipses (`...`) in email templates are now surrounded by square brackets (i.e., `[...]`) to avoid confusion about whether or not the ellipsis was part of the comment content. See [Issue #77](https://github.com/websharks/comment-mail/issues/77).
- **Enhancement**: In the email templates, the "Add Reply" links have been changed to "Reply" and when the reply is in response to someone and we have the authors name, the link includes the authors name (e.g., "Reply to <author-name>"). See [Issue #77](https://github.com/websharks/comment-mail/issues/77).
- **Enhancement**: Updated email templates to use `\WP_Comment` instead of `\stdClass` where appropriate. See [Issue #77](https://github.com/websharks/comment-mail/issues/77).
- **Enhancement**: The manual "Add New Subscription" functionality has been hidden from the front-end of the site (i.e., hidden from the "My Comment Subscriptions" page that subscribers use to manage their subscriptions, and from email templates). Note that this change has no effect on the subscription options that appear on the comment form itself. See discussion in [Issue #108](https://github.com/websharks/comment-mail/issues/108#issuecomment-161462209).
- **WordPress v4.4 Compatibility:** Fixed a compatibility bug with WordPress v4.4. Props @jaswsinc. See [Issue #170](https://github.com/websharks/comment-mail/issues/170).
- **Jetpack Compatibility:** This release fixes a bug in the automatic Jetpack conflict detection; i.e., if you enable Jetpack Subscriptions w/ Follow Comments enabled together with Comment Mail at the same, a warning is displayed to notify you of a possible conflict in your configuration. See [Issue #113](https://github.com/websharks/comment-mail/issues/113).

= v150709 =

- Initial release.
