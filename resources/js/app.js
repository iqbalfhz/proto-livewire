import Quill from "quill";

// Register HR / Divider blot
const BlockEmbed = Quill.import("blots/block/embed");
class DividerBlot extends BlockEmbed {
    static blotName = "divider";
    static tagName = "hr";
}
Quill.register(DividerBlot);

// Quill 2 already registers "modules/table" itself — re-registering it here is
// what produced the "Overwriting formats/table" console warnings. The editor
// enables it through `modules: { table: true }`.
window.Quill = Quill;
