import Quill from "quill";
import Table from "quill/modules/table";

// Register HR / Divider blot
const BlockEmbed = Quill.import("blots/block/embed");
class DividerBlot extends BlockEmbed {
    static blotName = "divider";
    static tagName = "hr";
}
Quill.register(DividerBlot);

Quill.register({ "modules/table": Table }, true);
window.Quill = Quill;
