set(testing_dir "${CDash_SOURCE_DIR}/tests")
set(binary_testing_dir "${CDash_BINARY_DIR}/tests")

# function to add a new PHP based coverage test to CDash
#
function(add_php_test TestName)
  add_test(
    ${TestName}
    ${PHP_EXE} ${testing_dir}/singletest.php ${testing_dir}/test_${TestName}.php
  )
  set_tests_properties(
    ${TestName} PROPERTIES
    FAIL_REGULAR_EXPRESSION ".*Failures: [1-9]+.*;.*Exceptions: [1-9]+.*"
  )
endfunction(add_php_test)
function(add_configured_php_test TestName)
  add_test(
    ${TestName}
    ${PHP_EXE} ${testing_dir}/singletest.php ${binary_testing_dir}/test_${TestName}.php
  )
  set_tests_properties(
    ${TestName} PROPERTIES
    FAIL_REGULAR_EXPRESSION ".*Failures: [1-9]+.*;.*Exceptions: [1-9]+.*"
  )
endfunction(add_configured_php_test)

add_php_test(install)
add_php_test(compressedtest)
add_php_test(createpublicdashboard)
add_php_test(email)
add_php_test(projectwebpage)
add_php_test(subproject)
add_php_test(actualtrilinossubmission)

add_php_test(upgrade)
add_php_test(banner)
add_php_test(buildconfigure)
add_php_test(builderrordiff)
add_php_test(buildgroupposition)
add_php_test(buildgrouprule)
add_php_test(buildoverview)
add_php_test(buildtestdiff)
add_php_test(buildusernote)
add_php_test(committerinfo)
add_php_test(dailyupdatefile)
add_php_test(edituser)
add_php_test(errorlog)
add_php_test(image)
add_php_test(displayimage)
add_php_test(import)
add_php_test(importbackup)
add_php_test(importbuilds)
add_php_test(iphone)
add_php_test(loggingadministration)
add_php_test(login)
add_php_test(managebanner)
add_php_test(managebackup)
add_php_test(manageclient)
add_php_test(manageprojectroles)
add_php_test(manageusers)
add_php_test(processsubmissions)
add_php_test(projectindb)
add_php_test(pubproject)
add_php_test(projectmodel)
add_php_test(querytests)
add_php_test(router)
add_php_test(sitestatistics)
add_php_test(gitinfo)
add_php_test(testenv)
add_php_test(testoverview)
add_php_test(userstatistics)
add_php_test(user)
add_php_test(viewchanges)
add_php_test(viewconfigure)
add_php_test(viewdynamicanalysis)
add_php_test(viewdynamicanalysisfile)
add_php_test(viewerrorlog)
add_php_test(viewissues)
add_php_test(viewmap)

add_php_test(groupsdescription)
add_php_test(viewsubprojectdependencies)
add_php_test(testmodel)
add_php_test(buildmodel)
add_php_test(projectxmlsequence)
add_php_test(uploadfile)
add_php_test(branchcoverage)
add_php_test(javajsoncoverage)
add_php_test(jscovercoverage)

add_subdirectory(ctest)

add_subdirectory(js/e2e_tests)

if(CDASH_USE_SELENIUM)
  file(MAKE_DIRECTORY ${CDash_BINARY_DIR}/cmake.git)
  add_subdirectory(selenium)
endif()

# These should go last so the removal of builds doesn't clobber other tests
add_subdirectory(autoremovebuilds)
