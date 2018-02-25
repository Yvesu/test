import React, { Component } from 'react'
import { Layout,Spin } from 'antd'
import LibrarySteps from './libraryUpload/LibrarySteps'
class LibraryUpload extends Component{
  render(){
    return(
      <Layout className="library_upload_box">
        <h3>发布混合资源</h3>
        <LibrarySteps />
      </Layout>
    )
  }
}

export default LibraryUpload
