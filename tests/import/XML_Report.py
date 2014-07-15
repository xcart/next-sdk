#!/usr/bin/env python

import xml.etree.ElementTree as ET

"""
The script can be used to output arbitrary data in Junit-compatible format.
Data in main() is for test purposes.
"""

class XML_Report:

    def __init__(self, suite, cases, filename="./logs/import.xml"):
        self.suite = suite
        self.cases = cases
        self.filename = filename 
        self.build_xml()

    def build_xml(self):

        root = ET.Element("testsuites")
        testsuite = ET.SubElement(root, "testsuite")
        testsuite.set("name", self.suite['name'])
        testsuite.set("tests", str(len(self.cases)))
        for case in self.cases: 
            testcase = ET.SubElement(testsuite, "testcase")
            testcase.set("name", case['name'])
            if case['failure']:
                failure = ET.SubElement(testcase, "failure")
                failure.text = case['failure']
            if case['time']:
                testcase.set("time", case['time'])
        tree = ET.ElementTree(root)
        tree.write(self.filename)

def main():
    suite1 = {'name': 'Import'} 
    cases1 = [{'failure': '', 'name': 'products-2014-04-29.csv', 'time': '5.2'}, {'failure': '--- ./expected/customers-2014-04-29.csv_expected\n+++ ./result/customers-2014-04-29.csv_result\n@@ -1,7 +1,7 @@\n Array\n (\n         (\n-            [type:protected] => A \n+            [type:protected] => E\n             [code:protected] => USER-LOGIN-FMT\n             [arguments:protected] => Array\n                 (\n', 'name': 'customers-2014-04-29.csv', 'time': '0.5'}, {'failure': '', 'name': 'categories-2014-04-29.csv', 'time': '1.0'}]
    suite2 = {'name': 'asdf'}
    cases2 = [{'name': 'test1', 'failure': '', 'time': '0'}, {'name': 'test2', 'failure': 'something happened!', 'time': ''}]

    myxml1 = XML_Report(suite1, cases1, "./logs/import1.xml")
    myxml2 = XML_Report(suite2, cases2, "./logs/import2.xml")

if __name__ == '__main__':
    main()


