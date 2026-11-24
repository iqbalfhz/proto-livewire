import "./editorial.js";

// Quill is heavy and only the admin post editor needs it.
window.initPostEditor = async ($wire) => {
    const { initPostEditor } = await import("./post-editor.js");

    initPostEditor($wire);
};
