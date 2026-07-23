import re

file_path = 'resources/views/projects/show.blade.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Pattern to find the img tags for reference_file and final_designs
# Example: <img src="{{ ->reference_file }}" class="rtb-ref-preview" style="margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ ->reference_file }}', false)" title="View Image">

pattern = re.compile(r'<img\s+src="\{\{\s*(\$[a-zA-Z0-9_]+->(?:reference_file|final_designs))\s*\}\}"\s+class="rtb-ref-preview"(.*?)>')

def replacement(match):
    var_name = match.group(1)
    rest_of_tag = match.group(2)
    
    # We will replace title="View Image" with title="View Video" in the video tag
    video_rest = rest_of_tag.replace('title="View Image"', 'title="View Video"')
    
    return f'''@if(preg_match('/\\.(mp4|webm|ogg|mov)$/i', {var_name}))
                                                <video src="{{{{ {var_name} }}}}" class="rtb-ref-preview"{video_rest} preload="metadata"></video>
                                            @else
                                                <img src="{{{{ {var_name} }}}}" class="rtb-ref-preview"{rest_of_tag}>
                                            @endif'''

new_content = pattern.sub(replacement, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(new_content)
print("Updated all rtb-ref-preview img tags.")
