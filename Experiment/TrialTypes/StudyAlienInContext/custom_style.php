<style>
    #content { width: auto; }
    
    .context_container { position: relative; }
    
    .canvas_container {
        display: none;
        position: absolute;
        top: 0;
        bottom: 0;
        width: 50%;
    }
    
    .canvas_container.side_left  { display: block; left: 0; }
    .canvas_container.side_right { display: block; left: 50%; }
    .canvas_container.centered   { display: block; left: 25%; }
    
    .canvas_container > div {
        display: table;
        height: 100%;
        width: 100%;
    }
    .canvas_container > div > div {
        display: table-cell;
        text-align: center;
        vertical-align: middle;
    }
    
    .feedback {
        display: none;
        font-size: 150%;
        max-width: 900px;
        margin: auto;
    }
    .warning {
        visibility: hidden;
        text-decoration: underline;
        color: #400;
    }
</style>
