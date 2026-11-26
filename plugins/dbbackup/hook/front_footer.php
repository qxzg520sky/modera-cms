<?php
/**
 * 前台钩子：到点自动备份
 * 不输出任何内容，只做“定时备份”的触发点——有访客访问前台时顺手检查一次，
 * 这样不依赖系统的计划任务（cron）。没到间隔时间只读一个 state.json，几乎零开销。
 */

\plugins\dbbackup\service\Backup::maybeAutoRun();
