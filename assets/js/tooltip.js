/**
 * 轻量级词汇表工具提示脚本
 * 使用原生 JavaScript 实现悬停功能
 */

/**
 * 把文本切分成多行（决定在哪里换行）
 * @param {string} text - 需要格式化的文本
 * @returns {string[]} 各行文本组成的数组
 */
function formatTextLines(text) {
    var lines = [];
    // 检查是否有标点符号
    var hasPunctuation = /[，。！？；：,.;:!?]/.test(text);

    if (hasPunctuation) {
        // 有标点符号，在每个标点符号后换行，但如果不超过5个字符不换行
        var lastIndex = 0;
        for (var i = 0; i < text.length; i++) {
            if (/[，。！？；：,.;:!?]/.test(text[i])) {
                var segment = text.substring(lastIndex, i + 1);
                if (segment.length > 5) {
                    lines.push(segment);
                    lastIndex = i + 1;
                }
            }
        }
        // 添加剩余部分
        if (lastIndex < text.length) {
            lines.push(text.substring(lastIndex));
        }
    } else if (text.length > 7) {
        // 没有标点符号且字数超过7，每5个字符换行
        for (var j = 0; j < text.length; j += 5) {
            lines.push(text.substring(j, j + 5));
        }
    } else {
        // 字数不超过7，不换行
        lines.push(text);
    }

    return lines;
}

/**
 * 动态调整工具提示高度
 * @param {HTMLElement} tooltip - 工具提示元素
 * @param {string} content - 工具提示内容
 */
function adjustTooltipHeight(tooltip, content) {
    // 固定宽度，高度由内容自动撑开，避免文字被上下裁切
    var fixedWidth = 120; // 固定宽度
    tooltip.style.maxWidth = fixedWidth + 'px';
    tooltip.style.width = fixedWidth + 'px';
    tooltip.style.height = 'auto';
}

document.addEventListener('DOMContentLoaded', function() {
    // 获取所有词汇表术语
    var glossaryTerms = document.querySelectorAll('.wordnest-term');
    var activeTerm = null;

    function openTooltip(term, sticky) {
        if (activeTerm && activeTerm !== term) {
            closeTooltip(activeTerm);
        }

        term.classList.add('wordnest-tooltip-open');
        term.setAttribute('aria-expanded', 'true');

        if (sticky) {
            term.setAttribute('data-wordnest-sticky', 'true');
        }

        activeTerm = term;
    }

    function closeTooltip(term) {
        if (!term) {
            return;
        }

        term.classList.remove('wordnest-tooltip-open');
        term.setAttribute('aria-expanded', 'false');
        term.removeAttribute('data-wordnest-sticky');

        if (activeTerm === term) {
            activeTerm = null;
        }
    }
    
    // 处理每个术语
    glossaryTerms.forEach(function(term, index) {
        // 从数据属性获取工具提示内容
        var tooltipContent = term.getAttribute('data-tooltip');
        
        if (tooltipContent) {
            var tooltipId = 'wordnest-tooltip-' + index;

            term.setAttribute('tabindex', '0');
            term.setAttribute('aria-describedby', tooltipId);
            term.setAttribute('aria-expanded', 'false');

            // 创建工具提示元素
            var tooltip = document.createElement('span');
            tooltip.className = 'wordnest-tooltip';
            tooltip.id = tooltipId;
            tooltip.setAttribute('role', 'tooltip');
            
            // 用文本节点 + <br> 元素构建内容，避免 innerHTML 带来的 XSS 风险
            var lines = formatTextLines(tooltipContent);
            lines.forEach(function (line, index) {
                if (index > 0) {
                    tooltip.appendChild(document.createElement('br'));
                }
                tooltip.appendChild(document.createTextNode(line));
            });

            // 将工具提示作为术语的子元素，使其相对术语（position:relative）定位在正上方
            term.appendChild(tooltip);
            
            // 动态调整工具提示高度
            adjustTooltipHeight(tooltip, tooltipContent);

            term.addEventListener('mouseenter', function () {
                openTooltip(term, false);
            });

            term.addEventListener('mouseleave', function () {
                if (term.getAttribute('data-wordnest-sticky') !== 'true') {
                    closeTooltip(term);
                }
            });

            term.addEventListener('focus', function () {
                openTooltip(term, false);
            });

            term.addEventListener('blur', function () {
                closeTooltip(term);
            });

            term.addEventListener('click', function (event) {
                event.stopPropagation();

                if (term.classList.contains('wordnest-tooltip-open') && term.getAttribute('data-wordnest-sticky') === 'true') {
                    closeTooltip(term);
                    return;
                }

                openTooltip(term, true);
            });

            term.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeTooltip(term);
                    term.blur();
                } else if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    if (term.classList.contains('wordnest-tooltip-open') && term.getAttribute('data-wordnest-sticky') === 'true') {
                        closeTooltip(term);
                    } else {
                        openTooltip(term, true);
                    }
                }
            });
        }
    });

    document.addEventListener('click', function () {
        closeTooltip(activeTerm);
    });
});
