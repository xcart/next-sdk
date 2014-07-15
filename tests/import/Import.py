#!/usr/bin/env python

import argparse
import difflib
import logging
import os
import re
import shutil
import subprocess
import sys
import time
import XML_Report

"""
The script uses warnings and errors recorded by imp.php,
removes irrelevant lines and creates a diff.
Test passes if diff is empty.
"""

class Import:

    def __init__(self, workspace, fname):
        self.workspace = workspace
        self.fname = fname
        self.ignoreFileChecking = '1' 

    def setup(self):
        """Remove /src/var/import and copy saved files to be imported.
            """
        target = os.path.join(self.workspace, 'src/var/import')
        source = os.path.join(self.workspace, '.dev/tests/import/data', self.fname)

        if os.path.exists(target):
            shutil.rmtree(target)
        os.mkdir(target)
        shutil.copy(source, target)

    def run_import(self):
        print "Running import..."
        log_file = os.path.join(self.workspace, '.dev/tests/import/log')
        start_time = time.time()
        output = subprocess.check_output(["php", "imp.php", self.ignoreFileChecking], cwd = os.path.join(self.workspace, '.dev/tests/import'))
        fw = open(log_file, 'w')
        fw.write(output)
        return time.time() - start_time

    def log_cleanup(self):
        print "Cleaning up import result..."
        log_in = os.path.join(self.workspace, '.dev/tests/import/log')
        log_out = os.path.join(self.workspace, '.dev/tests/import/result', self.fname+'_result')
        fr = open(log_in, 'r')
        fw = open(log_out, 'w')
        for line in fr:
            s1 = re.search(r'date:protected', line)
            s2 = re.search(r'id:protected', line)
            s3 = re.search(r'XLite\\Model\\ImportLog Object', line)
            if not s1 and not s2 and not s3:
                fw.write(line)

    def create_diff(self):
        print "Creating diff..."
        result = os.path.join(self.workspace, '.dev/tests/import/result', self.fname+'_result')
        expected = os.path.join(self.workspace, '.dev/tests/import/expected', self.fname+'_expected')
        diff_file = os.path.join(self.workspace, '.dev/tests/import/result', self.fname+'_diff')
        diff_str = ''
        with open(result, 'r') as result:
            with open(expected, 'r') as expected:
                diff = difflib.unified_diff(
                    expected.readlines(),
                    result.readlines(),
                    tofile='./result/'+self.fname+'_result',
                    fromfile='./expected/'+self.fname+'_expected',
                )
                fw = open(diff_file, 'w')
                for line in diff:
                    sys.stdout.write(line)
                    diff_str = diff_str + line
                    fw.write(line)
        if diff_str:
            print 'FAIL'
        else:
            print 'PASS'
        return diff_str

    def run(self):
        self.setup()
        runtime = self.run_import()
        self.log_cleanup()
        diff = self.create_diff()
        return (runtime, diff)


def parse_args():
    parser = argparse.ArgumentParser()
    parser.add_argument("--workspace", default='/home/vagrant/next', help="Path to app")
    args = parser.parse_args()
    return args

def main():

    args = parse_args()

    result_dir = os.path.join(args.workspace, '.dev/tests/import/result')
    if not os.path.exists(result_dir):
        os.mkdir(result_dir)

    suite = {'name': 'Import'}
    cases = []

    fnames = os.listdir(os.path.join(args.workspace, '.dev/tests/import/data'))
    for fname in sorted(fnames):
        print "Starting import test for %s" % fname
        runtime, diff = Import(args.workspace, fname).run() 
        cases.append({'name': fname, 'failure': diff, 'time': '%.1f' % runtime})

    xml_report = os.path.join(args.workspace, 'logs/import.xml')
    XML_Report.XML_Report(suite, cases, xml_report)


if __name__ == '__main__':
    main()

