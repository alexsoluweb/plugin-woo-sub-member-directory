import '../../scss/member-directory.scss'

class MemberDirectory {
  static init() {

    // Get the member directory element
    const memberDirectory = document.querySelector('#wsmd-member-directory');

    // Check if the member directory element exists
    if (!memberDirectory) {
      return;
    }
  }
}

// Main entry point
document.addEventListener('DOMContentLoaded', () => {
  MemberDirectory.init();
});
