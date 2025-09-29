## Task Instructions Outline
You are tasked with creating a structured outline for a documentation page in the Flight PHP framework to make it consistent, scannable, and user-friendly, similar to php.net or laravel.com’s predictable structure. The goal is to create a rich user experience where users know exactly where to find information (e.g., summary, examples) and to ensure the content is structured for easy parsing by AI models. The page must align with Flight’s core principles: fast (minimal overhead), simple (zero fuss, beginner-friendly), extensible (flexible for plugins/AI), and dependency-free. It must support PHP 7.4+ and avoid complex configurations or external dependencies.

You will perform your task in 3 steps:
1. Run an analysis of the provided source documentation to identify all key features, advanced usage, and caveats.
2. Output all the features/highlights you have discovered, categorized as potentially Basic or Advanced based on your best judgment (e.g., common everyday use vs. edge cases or specialized handling). If anything is unclear or if you need clarification on categorization, ask targeted questions only for those specific items. Ask for any missing details you feel are essential to finalize the structure. 
3. Create the documentation structure using the provided standardized template and save it to the file specified by the end user, ensuring all identified features and nuances are outlined with appropriate sections and placeholders. 
  - For Overview, Understanding, See Also, Troubleshooting, and Changelog, fill in the gaps with content you feel is relevant based on the provided source documentation and your own internal knowledge base.
  - For Basic Usage and Advanced Usage, do not fill in detailed content, code examples, or explanations—use placeholders instead. For each scenario, use a `###` heading for the subsection title, followed by a brief contextual description and a code block placeholder. Do not list the sub section name with a number at the beginning (e.g., "1. Simple Callback")—just use the title (e.g., "Simple Callback").
  - For code placeholders, use a comment that describes the example, e.g.,
    ```php
    // Example: Get a query string parameter
    ```
  - If original content lacks details for a section, use a placeholder like "N/A - Add details if applicable".
  - See the example below for section formatting:

    ### Simple Callback
    How to define a simple route callback in Flight.
    ```php
    // Example: Simple route callback
    ```

### Guidelines for the Structure Creation

**Summary:**  
Your outline must cover all key features, advanced usage, caveats, and examples from the source documentation by providing sections and placeholders for them. If in doubt, include a placeholder and note it for potential user input.

**1. Feature Parity & Completeness**
- Ensure every unique feature, advanced usage, and caveat from the source documentation has a corresponding section, subsection, or placeholder in the outline.  
- Do not omit any Flight-specific functionality, options, or edge cases—place them in appropriate sections like “Basic Usage” or “Advanced Usage”.

**2. Section-by-Section Mapping**
- For each major concept or feature in the source (e.g., Dependency Injection, route inspection, streaming, grouping, resource options, passing execution, router object usage), create a dedicated subsection or placeholder.
- If a feature is present in the source but not in the template, add it as a new subsection or placeholder.

**3. Advanced and Edge Cases**
- Provide subsections or placeholders for advanced features (e.g., passing execution to the next route, inspecting route info, grouping with middleware) and any Flight-specific caveats.

**4. Prompting for Missing Features**
- If you notice a feature in the source that doesn’t fit the template, add a new subsection or placeholder for it.

**5. Consistency and Formatting**
- Use Markdown with consistent headings, code block placeholders, tables, and links as described in the template.

**6. Validation**
- Assume all code placeholders will be filled with executable examples in Flight v3 and PHP 7.4+.
- If original content lacks details for a section, use a placeholder like “N/A - Add details if applicable”.

**7. Focus on Flight's Core Principles**
- In placeholders for explanations, note to emphasize simplicity, speed, and extensibility.
- Avoid unnecessary complexity or jargon in structure notes.

---

**Template Structure**  

## Overview
Placeholder for 1-2 concise sentences summarizing the concept, its purpose in Flight, and its key benefits.

## Understanding
Placeholder for 1-2 paragraphs explaining the concept in more detail, its importance, and how it fits into Flight’s architecture. (Focus on beginner-friendly analogies or metaphors if helpful.)

## Basic Usage
List the top most common scenarios as subsections, each with a `###` heading, a brief contextual description, and a code block placeholder. For example:

### Scenario Title
Brief contextual description.
```php
// Example: Description of code
```

(Continue for 3-5 scenarios based on analysis.)

## Advanced Usage
List advanced scenarios or edge cases as subsections, each with a `###` heading, a brief contextual description, and a code block placeholder. For example:

### Advanced Scenario Title
Brief contextual description and special handling notes.
```php
// Example: Description of advanced code
```

(Continue for 2-3 based on analysis.)

## See Also
- Placeholder for link to related Flight doc (e.g., /learn/middleware).
- Placeholder for link to php.net page (if relevant, e.g., preg_match for regex).
- Placeholder for external plugin example (if applicable).

(Use relative paths for Flight links. Scan the file tree of `content/v3/en/` for related topics and suggest based on relevance.)

## Troubleshooting
- Placeholder for common issue 1: Description and fix.
- Placeholder for common issue 2: Description and fix (e.g., suggest custom plugins without deps).

(Add bullets based on source caveats.)

## Changelog
- Placeholder for version X.Y.Z: Brief description of change.
- Placeholder for version X.Y.Z: Brief description of change.

(Use bullets in reverse chronological order; add based on any version history in source.)