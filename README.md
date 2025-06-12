<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" class="logo" width="120"/>

# Perplexity Pro Search for WordPress
###  v. 1.1

Integrate the power of [Perplexity Pro](https://www.perplexity.ai/) into your WordPress site with this plugin. Deliver domain-specific, AI-powered answers directly in your search results using Perplexity’s Sonar API. Enhance your site’s search experience with advanced answer generation, related article citations, and easy customization—all with simple shortcodes.

---

## Features

- **AI-Powered Answers:** Uses Perplexity’s Sonar API to provide direct, accurate answers to user queries.
- **Domain-Specific Search:** Filter results to your site or specific domains for highly relevant answers.
- **Related Articles:** Lists referenced articles as citations for every answer.
- **Customizable Search Box:** Adjust placeholder text, button label, and width via shortcode attributes.
- **Sidebar Support:** Add a compact search form to any sidebar or widget area.
- **Admin Controls:** Set your API key and manage temperature controls from the WordPress Admin panel.
- **Easy Integration:** Just add your API key and drop shortcodes where you want search functionality.

---

## Installation

1. **Download and Upload Plugin**
    - Download the plugin ZIP file.
    - In your WordPress admin, go to `Plugins > Add New > Upload Plugin`.
    - Select the ZIP file and click **Install Now**.
    - Activate the plugin.
2. **Configure Settings**
    - Go to `Settings > Perplexity Pro Search`.
    - Enter your Perplexity Sonar API key.
    - Set your preferred temperature (response creativity).

---

## Usage

### Main Search Block

Add the following shortcode to any page or post to display the main Perplexity-powered search and answer engine:

```plaintext
[perplexity_search]
```


#### Custom Attributes

You can customize the main search box with these attributes:


| Attribute | Description | Example Value |
| :-- | :-- | :-- |
| `placeholder` | Custom placeholder text | "Search Mexico News Daily..." |
| `button_text` | Custom text for the search button | "Search" |
| `width` | Width of the search box (e.g., 100%) | "100%", "500px" |

**Example:**

```plaintext
[perplexity_search placeholder="Search Mexico News Daily..." button_text="Search" width="100%"]
```


### Sidebar or Compact Search

Add a basic search box to your sidebar or any widget area with:

```plaintext
[sidebar_pp_search]
```


---

## How It Works

- **User submits a query** via the search box.
- **Plugin sends the query** to Perplexity’s Sonar API, filtered to your domain (if configured).
- **AI-generated answer** is displayed, along with a list of related articles cited as references.

- **Known image issues** there is currently an issue with the Article thumbnails in some cases. Working that out with Perplexity. Will update as soon as we can.
---

## Screenshots

<img src="https://github.com/user-attachments/assets/b2596b65-9c2b-408f-8d3a-aa3023f47571" alt="PP_WPSearch_2" width="50%">
<img src="https://github.com/user-attachments/assets/37494736-a43b-4793-a0ee-363e9ce3fbe9" alt="PP_WPSearch_2" width="50%">

---

## FAQ

**Q: Do I need a Perplexity API key?**
A: Yes, you must have a Perplexity Sonar API key. Get one from your Perplexity account dashboard.

**Q: Can I restrict search results to my domain?**
A: Yes, the plugin supports domain filtering for site-specific answers.

**Q: Can I customize the look of the search box?**
A: Yes, use the shortcode attributes to adjust placeholder text, button label, and width.

---

## Support

For issues or feature requests, please open an issue on this repository or contact the plugin author.

---

## License

MIT License. 

---

**Enjoy smarter search on your WordPress site with Perplexity Pro Search!**

