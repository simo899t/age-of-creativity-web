/* Editor view of the Creators timeline block. The real timeline is drawn on
   the live site; in the editor we show a summary card with links to where
   its content is managed, plus a few text settings in the sidebar. */
(function (wp) {
  var el = wp.element.createElement;
  var __ = wp.i18n.__;
  var be = wp.blockEditor;
  var c = wp.components;

  wp.blocks.registerBlockType('aoc/timeline', {
    edit: function (props) {
      var a = props.attributes;
      var set = props.setAttributes;
      var admin = (window.aocTimelineEditor && window.aocTimelineEditor.admin) || '/wp-admin/';
      var count = window.aocTimelineEditor ? window.aocTimelineEditor.count : null;

      return el('div', be.useBlockProps({ className: 'aoc-tl-editor' }),
        el(be.InspectorControls, null,
          el(c.PanelBody, { title: __('Timeline text', 'aoc') },
            el(c.ToggleControl, {
              label: __('Show the project title above the timeline', 'aoc'),
              checked: a.showTitle,
              onChange: function (v) { set({ showTitle: v }); }
            }),
            el(c.TextControl, {
              label: __('Small line above the title', 'aoc'),
              value: a.kicker,
              onChange: function (v) { set({ kicker: v }); }
            }),
            el(c.TextareaControl, {
              label: __('Reading hint under the timeline', 'aoc'),
              value: a.hint,
              onChange: function (v) { set({ hint: v }); }
            }),
            el('p', { className: 'components-base-control__help' },
              __('The title and research question come from Settings → General (Site Title and Tagline).', 'aoc'))
          )
        ),
        el('div', { className: 'aoc-tl-editor__card' },
          el('svg', { viewBox: '0 0 120 40', width: 120, height: 40, 'aria-hidden': true },
            el('rect', { x: 4, y: 8, width: 46, height: 4, rx: 2, fill: '#4C5FD5' }),
            el('rect', { x: 30, y: 18, width: 52, height: 4, rx: 2, fill: '#2F8F6B' }),
            el('rect', { x: 58, y: 28, width: 40, height: 4, rx: 2, fill: '#C2405A' }),
            el('rect', { x: 76, y: 8, width: 38, height: 4, rx: 2, fill: '#C08414' })
          ),
          el('strong', null, __('Creators timeline', 'aoc')),
          el('p', null,
            count === null ? __('The interactive timeline is shown here on the live site.', 'aoc')
              : wp.i18n.sprintf(__('The interactive timeline of %d creators is shown here on the live site.', 'aoc'), count)),
          el('p', { className: 'aoc-tl-editor__links' },
            el('a', { href: admin + 'edit.php?post_type=aoc_creator', target: '_blank' }, __('Edit creators', 'aoc')),
            ' · ',
            el('a', { href: admin + 'edit-tags.php?taxonomy=aoc_field&post_type=aoc_creator', target: '_blank' }, __('Fields & eras', 'aoc')),
            ' · ',
            el('a', { href: admin + 'edit.php?post_type=aoc_creator&page=aoc-timeline', target: '_blank' }, __('Timeline settings', 'aoc'))
          )
        )
      );
    },
    save: function () { return null; }
  });
})(window.wp);
