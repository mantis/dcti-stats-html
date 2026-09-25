<?php

/**
 * Handles the errors and warnings reported by PHP with exception of:
 * E_ERROR, E_PARSE, E_CORE_*, or E_COMPILE_*.
 *
 * @version 1.0
 */
class ErrorHandler {
    var $error_types = array(
        'WARNING' => 'STATS WARNING',
        'NOTICE' => 'STATS NOTICE',
        'ERROR' => 'STATS ERROR',
        'DEBUG' => 'DEBUG',
    );

    var $verbose = false;
    var $proceed_url = null;
    var $_previous_errors = false;

    /**
     * Construct a new error handler.
     */
    function __construct($verbose = false, $proceed_url = null) {
        $this->verbose = $verbose;
        $this->proceed_url = $proceed_url;
    }

    /**
     * The error handler callback function.
     *
     * @param errno  the error number.
     * @param errstr  the error message.
     * @param errfile  the file in which the error occured.
     * @param errline  the line number in which the error occured.
     * @param errcontext  the context in which the error occured (array).
     */
    function error_handler($errno, $errstr, $errfile, $errline, $errcontext = null) {
        # Check if error reporting is turned off.
        if (error_reporting() == 0) {
            return;
        }

        switch ($errno) {
            case E_WARNING:
                $this->inline_error($this->error_types['WARNING'], $errstr, $errfile, $errline, $errcontext);
                break;
            case E_NOTICE:
                $this->inline_error($this->error_types['NOTICE'], $errstr, $errfile, $errline, $errcontext);
                break;
            case E_USER_ERROR:
                $this->halt_error($this->error_types['ERROR'], $errstr, $errfile, $errline, $errcontext);
                break;
            case E_USER_WARNING:
                $this->inline_error($this->error_types['WARNING'], $errstr, $errfile, $errline, $errcontext);
                break;
            case E_USER_NOTICE:
                # Used for debugging.
                $this->inline_error($this->error_types['DEBUG'], $errstr, $errfile, $errline, $errcontext);
                break;
        }

        $this->_previous_errors = true;
    }

    /**
     * Displays an inline error message and continues processing.
     *
     * @param errtype  the error type as string.
     * @param errstr  the error message.
     * @param errfile  the file in which the error occured.
     * @param errline  the line number in which the error occured.
     * @param errcontext  the context in which the error occured (array).
     */
    function inline_error($errtype, $errstr, $errfile, $errline, $errcontext = null) {
        global $debug;

        echo '<p style="color: red;">' . $errtype . ': ' . nl2br(htmlentities($errstr)) . '</p>';
        if ($this->verbose == true || (isset($debug) && $debug == true)) { # verbose output
            echo '<p style="color: red;">In file <b>' . htmlentities($errfile) . '</b> at line <b>' . $errline . '</b></p>';
        }
    }

    /**
     * Displays an error and halts processing.
     *
     * @param errtype  the error type as string.
     * @param errstr  the error message.
     * @param errfile  the file in which the error occured.
     * @param errline  the line number in which the error occured.
     * @param errcontext  the context in which the error occured (array).
     */
    function halt_error($errtype, $errstr, $errfile, $errline, $errcontext = null) {
        global $debug;

        // Keep (and flush) whatever has already been buffered - typically
        // the page's header/nav/stylesheet - instead of discarding it, so
        // a fatal error mid-page still renders inside the normal layout
        // rather than as a bare, unstyled fragment. Many fatal errors
        // (eg. a DB connection failure) happen before header.inc has run
        // at all though, so there may be no <head>/stylesheet buffered
        // yet - detect that and provide a minimal styled document of our
        // own in that case.
        $output_buffer = ob_get_length() ? ob_get_contents() : '';
        $has_layout = (strpos($output_buffer, '<body') !== false);

        if (ob_get_length()) {
            ob_end_flush();
        }

        if (!$has_layout) {
            ?>
<!DOCTYPE html>
<html lang="en">
 <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>stats.distributed.net - error</title>
  <link rel="stylesheet" type="text/css" href="/css/default.css" />
 </head>
 <body class="bg-slate-50 text-slate-900 antialiased">
  <main class="flex min-h-screen items-center justify-center px-4 py-6">
            <?php
        }
        ?>
        <div class="mx-auto max-w-md rounded-lg border border-red-200 bg-red-50 p-6 text-center shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-red-700"><?php echo htmlentities($errtype); ?></p>
            <p class="mt-2 text-sm text-slate-700"><?php echo nl2br(htmlentities($errstr)); ?></p>
            <?php if ($this->proceed_url === null) { ?>
            <p class="mt-4 text-sm text-slate-500">There was an error processing your request.</p>
            <?php } else { ?>
            <p class="mt-4"><a class="text-sm font-medium text-indigo-600 hover:text-indigo-500" href="<?php echo htmlentities($this->proceed_url); ?>">Proceed</a></p>
            <?php } ?>
            <?php if ($this->verbose == true || (isset($debug) && $debug == true)) { # verbose output ?>
            <div class="mt-4 border-t border-red-200 pt-4 text-left text-xs text-slate-600">
                <p>Full path: <?php echo htmlentities($errfile); ?></p>
                <p>Line: <?php echo $errline; ?></p>
                <?php $this->print_context($errcontext); ?>
                <?php $this->print_stack_trace(2); ?>
            </div>
            <?php } ?>
        </div>
        <?php
        if (!$has_layout) {
            ?>
  </main>
 </body>
</html>
            <?php
        }

        exit();
    }

    /**
     * Prints the context contained in the variable.
     *
     * @param errcontext  an array containing a context, or a normal variable.
     * @access static
     */
    function print_context($errcontext) {
        if ($errcontext == null) return;
        ?>
        <table>
            <tr><th>Variable</th><th>Value</th><th>Type</th></tr>
            <?php
            # print normal variables
            foreach ($errcontext as $var => $val) {
                if (!is_array($val) && !is_object($val)) {
                    echo '<tr><td>' . $var . '</td><td>' . htmlentities((string)$val) . '</td><td>' . gettype($val) . '</td></tr>';
                }
            }
            # print arrays
            foreach ($errcontext as $var => $val) {
                if (is_array($val) && ($var != 'GLOBALS')) {
                    echo '<tr><td colspan="3" align="left"><br /><b>' . $var . '</b></td></tr>';
                    echo '<tr><td colspan="3">';
                    $this->print_context($val);
                    echo '</td></tr>';
                }
            }
            ?>
        </table>
        <?php
    }

    /**
     * Prints a stack trace to the current point in the code.
     *
     * @param unshift  the amount of frames to unshift from the stack trace.
     * @access static
     */
    function print_stack_trace($unshift = 1) {
        if (!is_int($unshift)) {
            $unshift = 1;
        }

        $stack = debug_backtrace();

        while ($unshift-- > 0) {
            array_shift($stack);
        }

        ?>
        <center>
            <table>
                <tr><th>Filename</th><th>Line</th><th>Function</th><th>Args</th></tr>
                <?php
                foreach ($stack as $frame) {
                    echo '<tr>';
                    echo '<td>' . htmlentities(isset($frame['file']) ? $frame['file'] : '<php core>') . '</td><td>' . (isset($frame['line']) ? $frame['line'] : '') . '</td><td>' . $frame['function'] . '</td>';
                    $args = array();
                    if (isset($frame['args'])) {
                        foreach($frame['args'] as $value) {
                            $args[] = $this->build_parameter_string($value);
                        }
                    }
                    echo '<td>( ' . htmlentities(implode(', ', $args)) . ' )</td></tr>';
                }
                ?>
            </table>
        </center>
        <?php
    }

    /**
     * Build a parameter list recursively.
     *
     * @param param  any type of variable.
     * @return the parameter list as a string.
     * @access static
     */
    function build_parameter_string($param) {
        if (is_array($param)) {
            $results = array();
            foreach ($param as $key => $value) {
                $results[] = '[' . $this->build_parameter_string($key) . '] => ' . $this->build_parameter_string($value);
            }
            return '{ ' . implode(', ', $results) . ' }';
        } else if (is_bool($param)) {
            if ($param) {
                return 'true';
            } else {
                return 'false';
            }
        } else if (is_float($param) || is_int($param)) {
            return $param;
        } else if (is_null($param)) {
            return 'null';
        } else if (is_object($param)) {
            $results = array();
            $class_name = get_class($param);
            $inst_vars = get_object_vars($param);
            foreach ($inst_vars as $name => $value) {
                $results[] = '[' . $name . '] => ' . $this->build_parameter_string($value);
            }
            return 'Object <' . $class_name . '> ( ' . implode(', ', $results) . ' )';
        } else if (is_string($param)) {
            return "'" . $param . "'";
        }
    }

    /**
     * Returns whether an error or warning has already occured.
     *
     * @return true if an error or warning has occured, false otherwise.
     */
    function error_handled() {
        return $this->_previous_errors;
    }
}

# construct and register the error handler.
# (PHP4 requires the =& operator, but PHP5 uses just = operator.)
$g_error_handler = new ErrorHandler();
set_error_handler(array($g_error_handler, 'error_handler'));

?>
