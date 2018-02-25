import React, { Component } from 'react'
import { Button, Menu, Dropdown, Icon} from 'antd'


//下拉菜单
class SortNormalMenu extends Component {
  constructor(props){
    super(props)
     this.state={
       visible:false
     }
  }
  render() {
    return (
      <Dropdown overlay={
        <Menu className="dropMenu" style={{textAlign:"center"}}>
          <Menu.Item  key="1">
            <Button>向上</Button>
          </Menu.Item>
          <Menu.Item  key="2">
            <Button>向下</Button>
          </Menu.Item>
          <Menu.Item  key="3" >
            <Button type="danger">停用</Button>
          </Menu.Item>
        </Menu>}
        trigger={['click']}>
              <Button type={this.props.operation ? "" : "primary"} >
                  {this.props.operation ? "操作" : "批量操作"}
                    <Icon type="down" />
              </Button>
      </Dropdown>
      )
  }

}

export default SortNormalMenu
