=== Comment Mail™ (WP Comment Subscriptions) ===

Stable tag: 150625
Requires at least: 4.0
Tested up to: 4.3-alpha
Text Domain: comment-mail

License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Contributors: WebSharks, JasWSInc, raamdev, anguz
Donate link: http://www.websharks-inc.com/r/wp-theme-plugin-donation/
Tags: comments, subscribe, comment subscription, comment subscriptions, comment subscribe, subscribe comments, comment, comment email, comment notification, notifications, notification

Email comment subscriptions for WordPress®

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

  The best part about this feature is that it requires no special server requirements, and very little configuration. This is because Comment Mail Pro has been integrated with Mandrill for RVE service. So all you need to do is follow the simple instructions to setup a Mandrill account. Then supply Comment Mail with the email address that you configure there.

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
3. Navigate to the **Comment Subs** panel & enable it.

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

  The best part about this feature is that it requires no special server requirements, and very little configuration. This is because Comment Mail Pro has been integrated with Mandrill for RVE service. So all you need to do is follow the simple instructions to setup a Mandrill account. Then supply Comment Mail with the email address that you configure there.

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

== Software Requirements ==

In addition to the [WordPress Requirements](http://wordpress.org/about/requirements/), Comment Mail™ requires the following minimum versions:

- PHP 5.3.2+
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

= v150625 =

Requires PHP v5.3.2+.

== Changelog ==

= v150625 =

- Initial release.
