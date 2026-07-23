<?php
\ = 'resources/views/projects/show.blade.php';
\ = file_get_contents(\);

// Replace reference_file for \
\ = <<<EOT
                                                @if(preg_match('/\.(mp4|webm|ogg|mov)$/i', \->reference_file))
                                                <video src="{{ \->reference_file }}" class="rtb-ref-preview" style="margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ \- preload="metadata"></video>
                                            @else
                                                <img src="{{ \->reference_file }}" class="rtb-ref-preview" style="margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ \->
                                            @endifreference_file }}', false)" title="View Image">
EOT;
\ = <<<EOT
                                                @if(preg_match('/\.(mp4|webm|ogg|mov)$/i', \->reference_file))
                                                <video src="{{ \->reference_file }}" class="rtb-ref-preview" style="margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ \->reference_file }}', false)" title="View Video" preload="metadata"></video>
                                            @else
                                                <img src="{{ \->reference_file }}" class="rtb-ref-preview" style="margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ \->reference_file }}', false)" title="View Image">
                                            @endif
EOT;
\ = str_replace(\, \, \);

// Replace final_designs for \
\ = <<<EOT
                                                    @if(preg_match('/\.(mp4|webm|ogg|mov)$/i', \->final_designs))
                                                <video src="{{ \->final_designs }}" class="rtb-ref-preview" style="border-color:rgba(16,185,129,0.3);" onclick="event.stopPropagation(); openImagePreview('{{ \- preload="metadata"></video>
                                            @else
                                                <img src="{{ \->final_designs }}" class="rtb-ref-preview" style="border-color:rgba(16,185,129,0.3);" onclick="event.stopPropagation(); openImagePreview('{{ \->
                                            @endiffinal_designs }}', false)" title="View Image">
EOT;
\ = <<<EOT
                                                    @if(preg_match('/\.(mp4|webm|ogg|mov)$/i', \->final_designs))
                                                <video src="{{ \->final_designs }}" class="rtb-ref-preview" style="border-color:rgba(16,185,129,0.3);" onclick="event.stopPropagation(); openImagePreview('{{ \->final_designs }}', false)" title="View Video" preload="metadata"></video>
                                            @else
                                                <img src="{{ \->final_designs }}" class="rtb-ref-preview" style="border-color:rgba(16,185,129,0.3);" onclick="event.stopPropagation(); openImagePreview('{{ \->final_designs }}', false)" title="View Image">
                                            @endif
EOT;
\ = str_replace(\, \, \);

// Replace reference_file for \
\ = <<<EOT
                                                @if(preg_match('/\.(mp4|webm|ogg|mov)$/i', \->reference_file))
                                                <video src="{{ \->reference_file }}" class="rtb-ref-preview" onclick="event.stopPropagation(); openImagePreview('{{ \- preload="metadata"></video>
                                            @else
                                                <img src="{{ \->reference_file }}" class="rtb-ref-preview" onclick="event.stopPropagation(); openImagePreview('{{ \->
                                            @endifreference_file }}', false)" title="View Image">
EOT;
\ = <<<EOT
                                                @if(preg_match('/\.(mp4|webm|ogg|mov)$/i', \->reference_file))
                                                <video src="{{ \->reference_file }}" class="rtb-ref-preview" onclick="event.stopPropagation(); openImagePreview('{{ \->reference_file }}', false)" title="View Video" preload="metadata"></video>
                                            @else
                                                <img src="{{ \->reference_file }}" class="rtb-ref-preview" onclick="event.stopPropagation(); openImagePreview('{{ \->reference_file }}', false)" title="View Image">
                                            @endif
EOT;
\ = str_replace(\, \, \);

// Replace final_designs for \
\ = <<<EOT
                                                    @if(preg_match('/\.(mp4|webm|ogg|mov)$/i', \->final_designs))
                                                <video src="{{ \->final_designs }}" class="rtb-ref-preview" style="border-color:rgba(16,185,129,0.3);" onclick="event.stopPropagation(); openImagePreview('{{ \- preload="metadata"></video>
                                            @else
                                                <img src="{{ \->final_designs }}" class="rtb-ref-preview" style="border-color:rgba(16,185,129,0.3);" onclick="event.stopPropagation(); openImagePreview('{{ \->
                                            @endiffinal_designs }}', false)" title="View Image">
EOT;
\ = <<<EOT
                                                    @if(preg_match('/\.(mp4|webm|ogg|mov)$/i', \->final_designs))
                                                <video src="{{ \->final_designs }}" class="rtb-ref-preview" style="border-color:rgba(16,185,129,0.3);" onclick="event.stopPropagation(); openImagePreview('{{ \->final_designs }}', false)" title="View Video" preload="metadata"></video>
                                            @else
                                                <img src="{{ \->final_designs }}" class="rtb-ref-preview" style="border-color:rgba(16,185,129,0.3);" onclick="event.stopPropagation(); openImagePreview('{{ \->final_designs }}', false)" title="View Image">
                                            @endif
EOT;
\ = str_replace(\, \, \);

file_put_contents(\, \);
echo "Fixed via exact replacement.\n";
