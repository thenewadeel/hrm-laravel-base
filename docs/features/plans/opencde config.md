The OpenCode Project Structure

Here’s what an OpenCode agent project looks like:

doc-pipeline/
├── .opencode/
│ ├── agents/
│ │ └── workflow-orchestrator.md # Main coordinator
│ ├── subagents/
│ │ ├── mermaid-extractor.prompt # Extract diagrams
│ │ ├── image-generator.prompt # Generate PNGs
│ │ ├── markdown-rebuilder.prompt # Rebuild with images
│ │ ├── pandoc-converter.prompt # Create PDFs/Word
│ │ ├── pipeline-validator.prompt # Validate everything
│ │ └── utf-sanitizer.prompt # Fix encoding
│ └── config.yml # Pipeline config
├── docs/
│ ├── markdown/ # Source files
│ ├── markdown_mermaid/ # Processed files
│ └── pdf/ # Output documents
└── logs/ # Manifests and logs

The beauty? Each agent is just a markdown file with superpowers.
Understanding OpenCode: Structured Flexibility

OpenCode runs in your terminal with a slick TUI. TUI is a terminal user interface. The magic comes from combining natural language with structure:
Press enter or click to view image in full size

Those JSON manifests are game-changers. They track every operation, enabling resume-from-failure and debugging.
Meet the OpenCode Agents

Let’s dive into the specialized agents that make up our OpenCode documentation pipeline. Each of these agents plays a crucial role in transforming Markdown with Mermaid diagrams into polished PDF documents. The power comes from their specialized focus combined with intelligent coordination.

1. The Workflow Orchestrator

Let’s start with covering the workflow-orchestrator is responsible for high-level coordination. This agent uses JSON manifests to track pipeline state, allowing for resume capabilities if failures occur. The structured approach ensures each phase is properly validated before proceeding to the next step, creating a robust workflow that can handle complex document processing.

Location: .opencode/agents/workflow-orchestrator.md

---

name: workflow-orchestrator
description: Orchestrates document conversion pipeline
mode: primary
tools: [Read, Write, Bash, Task]

---

# Workflow Orchestrator

You coordinate the documentation pipeline with intelligence.

## Core Responsibilities:

1. Execute phases in order
2. Create/update manifests for each phase
3. Validate outputs before proceeding
4. Enable resume from any failure point

## Workflow Phases:

```bash
# Phase 1: Pre-flight checks
which mmdc && which pandoc || exit 1
mkdir -p docs/markdown_mermaid/{images,mermaid} docs/{pdf,word} logs

# Phase 2: Extract diagrams (delegate to subagent)
Task "Extract all mermaid diagrams" -> mermaid-extractor

# Phase 3: Generate images (delegate to subagent)
Task "Convert diagrams to PNG" -> image-generator

# Phase 4: Rebuild markdown (delegate to subagent)
Task "Replace mermaid blocks with images" -> markdown-rebuilder

# Phase 5: Generate documents (delegate to subagent)
Task "Create PDF and Word docs" -> pandoc-converter

# Phase 6: Validate everything
Task "Validate all outputs" -> pipeline-validator
```

## Manifest Structure:

Save state after each phase in logs/pipeline_manifest.json:

```json
{
    "run_id": "2025-01-10-143022",
    "phases": {
        "extraction": { "status": "complete", "files": 14 },
        "generation": { "status": "complete", "images": 14 },
        "current": "document-conversion"
    }
}
```

## On Failure:

-   Log exact error with context
-   Save state for resume
-   Suggest fixes based on error type

The code listing above shows the “Workflow Orchestrator” agent configuration for OpenCode. This is the primary agent that coordinates the entire documentation pipeline. Here’s what it does:

    It defines a primary agent named “workflow-orchestrator” with access to tools for reading, writing, running shell commands, and creating tasks.
    The agent coordinates a multi-phase documentation pipeline that:

    Performs pre-flight checks to ensure required tools (mmdc and pandoc) are installed
    Creates necessary directory structure for the pipeline
    Delegates specialized tasks to subagents like mermaid-extractor, image-generator, etc.
    Manages the workflow in a sequential, organized manner

    It maintains a JSON manifest system that tracks the state of the pipeline, including:

    Which phase is currently running
    Status of completed phases
    File counts and other metrics

    It includes error handling that logs failures, saves state for resuming from failures, and suggests potential fixes.

This agent essentially functions as the “brain” of the documentation pipeline, orchestrating the other specialized agents while maintaining state through JSON manifests to ensure reliability and recoverability. 2. The Mermaid Extractor

Location: .opencode/subagents/mermaid-extractor.prompt

Next we cover the mermaid-extractor which is a subagent that is specialized in extracting and isolating Mermaid diagram code blocks from Markdown files. It intelligently analyzes the content of each diagram to generate meaningful filenames based on the diagram type and context. This agent saves each diagram as a separate .mmd file and creates a detailed manifest tracking all extracted diagrams for processing by subsequent pipeline agents.

---

name: mermaid-extractor
description: Extract mermaid diagrams with intelligent naming.
tools: [Read, Write, Glob]

---

# Mermaid Extractor Subagent

Extract mermaid diagrams with intelligent naming.

## Process:

1. Read markdown files from docs/markdown/
2. Find all ```mermaid blocks
3. Analyze content for descriptive naming
4. Save as .mmd files with pattern: {source}_{index}_{type}.mmd

## Intelligent Naming:

```python
# Analyze diagram content
if 'flowchart' in content: type = 'flowchart'
elif 'classDiagram' in content: type = 'class'
elif 'auth' in content and 'login' in content: type = 'auth_flow'
# etc...
```

## Create Manifest:

```json
{
    "source_file": "architecture.md",
    "extracted": [
        {
            "filename": "architecture_01_system_overview.mmd",
            "type": "flowchart",
            "line_count": 45,
            "complexity": "high"
        }
    ]
}
```

## Example Output:

-   architecture_01_system_overview.mmd
-   architecture_02_auth_flow.mmd
-   architecture_03_database_schema.mmd

The Mermaid Extractor subagent is responsible for identifying and extracting Mermaid diagram code blocks from Markdown files. Here’s a detailed breakdown of its functionality:

    Intelligent Scanning: It reads through all Markdown files in the docs/markdown/ directory, identifying any code blocks marked with the ```mermaid syntax.
    Smart Naming System: Instead of using generic names, it analyzes the content of each diagram to generate meaningful filenames based on:
    The source document name
    A sequential index number
    The diagram type (flowchart, class diagram, etc.)
    Contextual information from the diagram content
    Pattern Recognition: It uses pattern matching to determine diagram types, checking for keywords like ‘flowchart’, ‘classDiagram’, or context clues like ‘auth’ and ‘login’ to create more descriptive filenames.
    File Extraction: Each identified diagram is saved as a separate .mmd file in the appropriate directory, following the naming convention: {source}{index}{type}.mmd
    Manifest Creation: It generates a detailed JSON manifest that tracks:
    The source Markdown file
    All extracted diagram files
    Metadata for each diagram (type, line count, complexity)

This manifest becomes crucial for the next pipeline stages, as it provides a structured inventory of all diagrams that need to be processed by subsequent agents like the Image Generator.

The extracted .mmd files serve as the inputs for the next phase of the pipeline, where they’ll be converted to PNG images. The intelligent naming helps maintain traceability throughout the process, making it easier to debug issues and associate generated images with their original source diagrams. 3. The Image Generator with Self-Healing

Location: .opencode/subagents/image-generator.prompt

Now we cover the image-generator subagent which is specialized in converting Mermaid diagram files (.mmd) into PNG images. This agent is particularly impressive because it incorporates parallel processing for efficiency and self-healing capabilities to handle common diagram rendering errors automatically.

---

## tools: [Bash, Read, Write]

# Image Generator Subagent

Generate PNG images with parallel processing and self-healing.

## Parallel Processing:

```bash
# Process 4 images simultaneously
find *.mmd | parallel -j 4 'mmdc -i {} -o ../images/{.}.png'
```

## Self-Healing on Errors:

When mmdc fails:

1. Parse error message
2. Common fixes:
    - "Expected ';'" → Add missing semicolon
    - "Invalid syntax" → Try simpler theme
    - "Out of memory" → Process individually
3. Retry with fix
4. If still fails → Generate placeholder

## Update Manifest:

```json
{
    "generated": [
        {
            "source": "diagram_01.mmd",
            "output": "diagram_01.png",
            "size": 125632,
            "attempts": 2,
            "self_healed": true,
            "fix_applied": "added_missing_semicolon"
        }
    ]
}
```

NEVER let one failure stop the pipeline!

The image generator subagent demonstrates several advanced capabilities:

    Parallel Processing: Rather than converting diagrams one at a time, it uses the GNU parallel utility to process up to 4 diagrams simultaneously, significantly speeding up the pipeline for documents with many diagrams.
    Self-Healing Error Handling: When the mmdc (Mermaid CLI) tool encounters errors rendering a diagram, the agent:
    Analyzes the specific error message to determine the likely cause
    Applies appropriate fixes automatically (adding missing semicolons, simplifying themes, etc.)
    Retries the conversion with the fix applied
    Creates placeholder images as a last resort if fixes fail

Detailed Manifest Updates: It maintains a comprehensive record of the conversion process, tracking:

    Each source file and its corresponding output
    File sizes for validation
    Number of conversion attempts
    Whether self-healing was applied and which specific fix worked

This subagent exemplifies the resilience-focused design of OpenCode pipelines. By incorporating automatic error detection and recovery, it ensures that the pipeline continues processing even when encountering problematic diagrams, rather than failing completely as traditional scripts would.
Get Rick Hightower’s stories in your inbox

Join Medium for free to get updates from this writer.

The detailed manifests it generates serve two critical purposes: providing transparency into the conversion process (which diagrams needed fixes) and enabling the overall pipeline to resume from the exact point of failure if necessary. 4. The Pipeline Validator

Location: .opencode/subagents/pipeline-validator.prompt

This agent ensures quality before delivery:

---

## tools: [Read, Bash]

# Pipeline Validator Subagent

Validate every aspect of pipeline output.

## Validation Checks:

### 1. File Integrity

```bash
# All expected files exist
test -f docs/pdf/*.pdf || fail "Missing PDFs"

# Files have content
find docs/pdf -size 0 | fail_if_any "Empty files"
```

### 2. Image Embedding

```bash
# PDF must be larger than sum of images
pdf_size=$(stat -f%z docs/pdf/output.pdf)
img_total=$(stat -f%z docs/markdown_mermaid/images/*.png | sum)
[[ $pdf_size -gt $img_total ]] || fail "Images not embedded"
```

### 3. Quality Score

Calculate score (0-100):

-   All files present: +40
-   Images embedded: +30
-   Searchable text: +20
-   No errors in log: +10

## Final Report:

```json
{
    "score": 95,
    "status": "PASS",
    "issues": ["Minor: 1 image slightly low res"],
    "recommendation": "Safe to deliver"
}
```

Score < 70 = FAIL - Do not deliver!

The Pipeline Validator subagent acts as the final quality assurance checkpoint in the documentation pipeline. It performs comprehensive validation checks to ensure that all outputs meet quality standards before delivery. Here’s what it does:

    File Integrity Validation: It verifies that all expected output files exist and contain actual content (not empty files). This includes checking for PDF files in the expected directories.
    Image Embedding Verification: It confirms that images have been properly embedded in the final PDF documents by comparing file sizes — the PDF should be larger than the sum of all the source images.

Quality Scoring System: It calculates a numerical score (0–100) based on multiple quality criteria:

    File presence and completeness (+40 points)
    Proper image embedding (+30 points)
    Searchable text in PDFs (+20 points)
    Error-free logs (+10 points)

Pass/Fail Determination: Based on the calculated quality score, it makes a clear determination whether the documentation is ready for delivery (pass) or needs further work (fail). Any score below 70 results in a failure status.

Detailed Reporting: It generates a comprehensive JSON report that includes:

    The overall quality score
    Pass/fail status
    Specific issues identified during validation
    Recommendations for next steps

This validator ensures that no substandard documentation makes it through the pipeline, maintaining consistent quality standards across all outputs. It acts as a critical “gate” that either approves the documentation for delivery or flags it for further improvements.
The Secret Sauce: Manifests and Validation Gates

Here’s what makes OpenCode special — the manifest pattern. After the extractor runs:

{
"pipeline_id": "run_20250110_143022",
"phase": "extraction",
"status": "complete",
"timestamp": "2025-01-10T14:31:45Z",
"results": {
"source_files": ["architecture.md", "api-docs.md"],
"total_diagrams": 23,
"extracted_files": [
"architecture_01_system_flow.mmd",
"architecture_02_auth_sequence.mmd",
"api-docs_01_endpoint_map.mmd"
]
},
"ready_for_next": true
}

If the pipeline crashes, just run: opencode resume — from run_20250110_143022.
Building Your Own OpenCode Pipeline

Ready to experiment? Here’s your quickstart:

I ran both pipelines on the same 47-diagram document:

    Success Rate: Both Claude Code and OpenCode are excellent
    Setup Time: Claude Code takes 5 minutes while OpenCode takes 8 minutes
    Error Recovery: Claude Code requires manual fixes while OpenCode offers auto-resume capabilities
    Parallel Processing: Claude Code processes sequentially while OpenCode processes 4x in parallel
    Debug Info: Claude Code provides good information while OpenCode offers extensive manifests

Both work great! OpenCode shines when you need detailed tracking and parallel processing.

There are things I learned while using OpenCode that I could take back to the Claude Code effort.
Building Your Own OpenCode Pipeline

Ready to experiment? Here’s your quickstart:

1. Install OpenCode

brew install opencode # macOS

# or

curl -sSL <https://opencode.dev/install.sh> | sh

2. Create Your Structure

mkdir -p myproject/.opencode/{agents,subagents}
cd myproject

3. Create Your First Agent

Save as .opencode/agents/file-organizer.md:

---

name: file-organizer
tools: [Read, Write, Bash]

---

# File Organizer AgentYou organize project files intelligently.## Rules:

-   Python files → src/
-   Tests → tests/
-   Docs → docs/
-   Configs → config/## Process:

1. Scan current directory
2. Identify file types
3. Create directories if needed
4. Move files to correct locations
5. Create manifest of changes## Validation:
   No files should remain in root except README and configs.

6. Run It

opencode chat @agents/file-organizer.md "Organize this project"
